#!/usr/bin/env php
<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Docs;

use ReflectionClass;
use Throwable;
const PKD_ROOT = __DIR__ . '/../..';
/**
 * Generates technical reference documentation from PHP source code.
 *
 * Usage: php docs/reference/generate.php
 *
 * Analyzes classes in cmd/, config/, public/, and src/ directories and
 * generates Markdown documentation files in docs/reference/.
 */

require_once PKD_ROOT . '/vendor/autoload.php';


/**
 * Extracts documentation from PHP files using reflection.
 */
final class DocGenerator
{
    private const array DIRECTORIES = ['cmd', 'config', 'public', 'src'];
    private const string OUTPUT_DIR = PKD_ROOT . '/docs/reference';

    /** @var array<string, ClassDoc> */
    private array $classes = [];

    /** @var array<string, array<string, mixed>> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $configFiles = [];

    /** @var array<string, string> */
    private array $cmdScripts = [];

    public function run(): void
    {
        echo "Scanning directories...\n";
        $this->scanDirectories();

        echo "Generating documentation...\n";
        $this->generateClassDocs();
        $this->generateRouteDocs();
        $this->generateConfigDocs();
        $this->generateCmdDocs();
        $this->generateIndex();

        echo "Done.\n";
    }

    private function scanDirectories(): void
    {
        foreach (self::DIRECTORIES as $dir) {
            $path = PKD_ROOT . '/' . $dir;
            if (!is_dir($path)) {
                continue;
            }
            $this->scanDirectory($path, $dir);
        }
    }

    private function scanDirectory(string $path, string $rootDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $relativePath = str_replace(PKD_ROOT . '/', '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            if ($rootDir === 'config') {
                $this->processConfigFile($file->getPathname(), $relativePath);
            } elseif ($rootDir === 'cmd') {
                $this->processCmdFile($file->getPathname(), $relativePath);
            } else {
                $this->processPhpFile($file->getPathname(), $relativePath);
            }
        }
    }

    private function processPhpFile(string $filepath, string $relativePath): void
    {
        $content = file_get_contents($filepath);

        // Extract namespace
        $namespace = $this->extractNamespace($content);
        if ($namespace === null) {
            return;
        }

        // Extract class/interface/trait names
        $declarations = $this->extractDeclarations($content);
        foreach ($declarations as $decl) {
            $fqcn = $namespace . '\\' . $decl['name'];

            try {
                if (!class_exists($fqcn) && !interface_exists($fqcn) && !trait_exists($fqcn)) {
                    continue;
                }

                $reflection = new ReflectionClass($fqcn);
                $classDoc = $this->extractClassDoc($reflection, $relativePath);
                $this->classes[$fqcn] = $classDoc;

                // Check for Route attributes
                $this->extractRoutes($reflection, $fqcn);
            } catch (Throwable $e) {
                // Skip classes that can't be reflected
                continue;
            }
        }
    }

    private function processConfigFile(string $filepath, string $relativePath): void
    {
        // Skip local config files
        if (str_contains($relativePath, '/local/')) {
            return;
        }

        $content = file_get_contents($filepath);
        $docblock = $this->extractFileDocblock($content);
        $description = $this->parseDocblock($docblock)['summary'] ?? '';

        $this->configFiles[$relativePath] = $description ?: $this->inferConfigPurpose($relativePath);
    }

    private function processCmdFile(string $filepath, string $relativePath): void
    {
        $content = file_get_contents($filepath);
        $docblock = $this->extractFileDocblock($content);
        $description = $this->parseDocblock($docblock)['summary'] ?? '';

        $this->cmdScripts[$relativePath] = $description ?: $this->inferCmdPurpose($relativePath);
    }

    private function extractNamespace(string $content): ?string
    {
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * @return array<array{type: string, name: string}>
     */
    private function extractDeclarations(string $content): array
    {
        $declarations = [];
        $pattern = '/(?:abstract\s+)?(?:final\s+)?(?:readonly\s+)?'
            . '(class|interface|trait|enum)\s+(\w+)/';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $declarations[] = [
                    'type' => $match[1],
                    'name' => $match[2],
                ];
            }
        }
        return $declarations;
    }

    private function extractClassDoc(ReflectionClass $ref, string $filepath): ClassDoc
    {
        $docblock = $ref->getDocComment() ?: '';
        $parsed = $this->parseDocblock($docblock);

        $methods = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $ref->getName()) {
                continue;
            }
            $methods[] = $this->extractMethodDoc($method);
        }

        $properties = [];
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            if ($prop->getDeclaringClass()->getName() !== $ref->getName()) {
                continue;
            }
            $properties[] = $this->extractPropertyDoc($prop);
        }

        $traits = array_map(
            fn(ReflectionClass $t) => $t->getName(),
            $ref->getTraits()
        );

        $interfaces = array_map(
            fn(ReflectionClass $i) => $i->getName(),
            $ref->getInterfaces()
        );

        $parent = $ref->getParentClass();

        return new ClassDoc(
            name: $ref->getName(),
            shortName: $ref->getShortName(),
            filepath: $filepath,
            type: $this->getClassType($ref),
            summary: $parsed['summary'] ?? '',
            description: $parsed['description'] ?? '',
            methods: $methods,
            properties: $properties,
            traits: $traits,
            interfaces: $interfaces,
            parent: $parent ? $parent->getName() : null,
            isAbstract: $ref->isAbstract(),
            isFinal: $ref->isFinal(),
        );
    }

    private function extractMethodDoc(\ReflectionMethod $method): MethodDoc
    {
        $docblock = $method->getDocComment() ?: '';
        $parsed = $this->parseDocblock($docblock);

        $parameters = [];
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $typeStr = $type ? $this->formatType($type) : 'mixed';

            $parameters[] = new ParameterDoc(
                name: $param->getName(),
                type: $typeStr,
                hasDefault: $param->isDefaultValueAvailable(),
                default: $param->isDefaultValueAvailable()
                    ? $this->formatDefaultValue($param->getDefaultValue())
                    : null,
                isVariadic: $param->isVariadic(),
                isNullable: $param->allowsNull(),
            );
        }

        $returnType = $method->getReturnType();

        $attributes = [];
        foreach ($method->getAttributes() as $attr) {
            $attributes[] = $attr->getName();
        }

        return new MethodDoc(
            name: $method->getName(),
            summary: $parsed['summary'] ?? '',
            description: $parsed['description'] ?? '',
            parameters: $parameters,
            returnType: $returnType ? $this->formatType($returnType) : 'void',
            throws: $parsed['throws'] ?? [],
            isStatic: $method->isStatic(),
            isAbstract: $method->isAbstract(),
            isFinal: $method->isFinal(),
            visibility: $this->getVisibility($method),
            attributes: $attributes,
            isApi: isset($parsed['tags']['api']),
        );
    }

    private function extractPropertyDoc(\ReflectionProperty $prop): PropertyDoc
    {
        $docblock = $prop->getDocComment() ?: '';
        $parsed = $this->parseDocblock($docblock);

        $type = $prop->getType();

        return new PropertyDoc(
            name: $prop->getName(),
            type: $type ? $this->formatType($type) : 'mixed',
            summary: $parsed['summary'] ?? '',
            hasDefault: $prop->hasDefaultValue(),
            default: $prop->hasDefaultValue()
                ? $this->formatDefaultValue($prop->getDefaultValue())
                : null,
            isReadonly: $prop->isReadOnly(),
            visibility: $this->getVisibility($prop),
        );
    }

    private function extractRoutes(ReflectionClass $ref, string $fqcn): void
    {
        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($method->getAttributes() as $attr) {
                if ($attr->getName() === 'FediE2EE\\PKDServer\\Meta\\Route') {
                    $args = $attr->getArguments();
                    $pattern = $args['uriPattern'] ?? $args[0] ?? '';
                    $this->routes[$pattern] = [
                        'class' => $fqcn,
                        'method' => $method->getName(),
                        'pattern' => $pattern,
                    ];
                }
            }
        }
    }

    /**
     * @return array{summary?: string, description?: string, throws?: string[], tags?: array<string, mixed>}
     */
    private function parseDocblock(string $docblock): array
    {
        if (empty($docblock)) {
            return [];
        }

        // Remove comment markers
        $lines = preg_split('/\r?\n/', $docblock);
        $cleaned = [];
        foreach ($lines as $line) {
            $line = preg_replace('/^\s*\/?\*+\/?/', '', $line);
            $cleaned[] = trim($line);
        }
        $cleaned = array_filter($cleaned, fn($l) => $l !== '');

        $summary = '';
        $description = '';
        $throws = [];
        $tags = [];
        $inDescription = false;

        foreach ($cleaned as $line) {
            if (str_starts_with($line, '@')) {
                $inDescription = false;
                if (preg_match('/@throws\s+(\S+)/', $line, $m)) {
                    $throws[] = $m[1];
                } elseif (preg_match('/@(\w+)(?:\s+(.*))?/', $line, $m)) {
                    $tags[$m[1]] = $m[2] ?? true;
                }
            } elseif (empty($summary)) {
                $summary = $line;
            } else {
                $inDescription = true;
                $description .= ($description ? ' ' : '') . $line;
            }
        }

        return [
            'summary' => $summary,
            'description' => $description,
            'throws' => $throws,
            'tags' => $tags,
        ];
    }

    private function extractFileDocblock(string $content): string
    {
        // Look for a docblock at the start of the file (after <?php)
        if (preg_match('/^<\?php[^\/]*?(\/\*\*.*?\*\/)/s', $content, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function formatType(\ReflectionType $type): string
    {
        if ($type instanceof \ReflectionUnionType) {
            return implode('|', array_map(
                fn($t) => $this->formatType($t),
                $type->getTypes()
            ));
        }

        if ($type instanceof \ReflectionIntersectionType) {
            return implode('&', array_map(
                fn($t) => $this->formatType($t),
                $type->getTypes()
            ));
        }

        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();
            $prefix = $type->allowsNull() && $name !== 'null' && $name !== 'mixed' ? '?' : '';
            return $prefix . $name;
        }

        return 'mixed';
    }

    private function formatDefaultValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        if (is_array($value)) {
            return '[]';
        }
        return (string)$value;
    }

    private function getClassType(ReflectionClass $ref): string
    {
        if ($ref->isInterface()) {
            return 'interface';
        }
        if ($ref->isTrait()) {
            return 'trait';
        }
        if ($ref->isEnum()) {
            return 'enum';
        }
        return 'class';
    }

    private function getVisibility(\ReflectionMethod|\ReflectionProperty $ref): string
    {
        if ($ref->isPrivate()) {
            return 'private';
        }
        if ($ref->isProtected()) {
            return 'protected';
        }
        return 'public';
    }

    private function inferConfigPurpose(string $path): string
    {
        $basename = basename($path, '.php');
        return match ($basename) {
            'certainty' => 'CA certificate bundle configuration',
            'ciphersweet' => 'CipherSweet encryption configuration',
            'database' => 'Database connection configuration',
            'hpke' => 'HPKE encryption configuration',
            'logs' => 'Logging configuration',
            'params' => 'Server parameters configuration',
            'redis' => 'Redis cache configuration',
            'routes' => 'HTTP routing configuration',
            'signing-keys' => 'Signing keys configuration',
            'twig' => 'Twig template engine configuration',
            'aux-type-allow-list' => 'Auxiliary data type allowlist',
            'aux-type-registry' => 'Auxiliary data type registry',
            default => 'Configuration file',
        };
    }

    private function inferCmdPurpose(string $path): string
    {
        $basename = basename($path, '.php');
        return match ($basename) {
            'init' => 'Initialize a new PKD server deployment',
            'init-database' => 'Initialize the database schema',
            'init-local-config' => 'Generate local configuration files',
            'cron-setup' => 'Set up cron jobs for scheduled tasks',
            'scheduled-tasks' => 'Run scheduled background tasks',
            default => 'Command-line script',
        };
    }

    private function generateClassDocs(): void
    {
        // Group classes by namespace
        $byNamespace = [];
        foreach ($this->classes as $fqcn => $doc) {
            $ns = substr($fqcn, 0, strrpos($fqcn, '\\'));
            $byNamespace[$ns][$fqcn] = $doc;
        }
        ksort($byNamespace);

        $output = "# Classes Reference\n\n";
        $output .= "This document provides technical reference for all classes in the PKD Server.\n\n";
        $output .= "## Table of Contents\n\n";

        foreach ($byNamespace as $ns => $classes) {
            $shortNs = str_replace('FediE2EE\\PKDServer\\', '', $ns);
            if (empty($shortNs)) {
                $shortNs = 'Root';
            }
            $anchor = strtolower(str_replace('\\', '-', $shortNs));
            $output .= "- [{$shortNs}](#{$anchor})\n";
        }
        $output .= "\n---\n\n";

        foreach ($byNamespace as $ns => $classes) {
            $shortNs = str_replace('FediE2EE\\PKDServer\\', '', $ns);
            if (empty($shortNs)) {
                $shortNs = 'Root';
            }
            $output .= "## {$shortNs}\n\n";

            ksort($classes);
            foreach ($classes as $fqcn => $doc) {
                $output .= $this->renderClassDoc($doc);
            }
        }

        file_put_contents(self::OUTPUT_DIR . '/classes.md', $output);
    }

    private function renderClassDoc(ClassDoc $doc): string
    {
        $modifiers = [];
        if ($doc->isAbstract) {
            $modifiers[] = 'abstract';
        }
        if ($doc->isFinal) {
            $modifiers[] = 'final';
        }
        $modifiers[] = $doc->type;

        $out = "### {$doc->shortName}\n\n";
        $out .= "**" . implode(' ', $modifiers) . "** `{$doc->name}`\n\n";
        $out .= "**File:** [`{$doc->filepath}`](../../{$doc->filepath})\n\n";

        if ($doc->summary) {
            $out .= "{$doc->summary}\n\n";
        }

        if ($doc->description) {
            $out .= "{$doc->description}\n\n";
        }

        if ($doc->parent) {
            $out .= "**Extends:** `{$doc->parent}`\n\n";
        }

        if (!empty($doc->interfaces)) {
            $interfaces = array_map(fn($i) => "`{$i}`", $doc->interfaces);
            $out .= "**Implements:** " . implode(', ', $interfaces) . "\n\n";
        }

        if (!empty($doc->traits)) {
            $traits = array_map(fn($t) => "`{$t}`", $doc->traits);
            $out .= "**Uses:** " . implode(', ', $traits) . "\n\n";
        }

        // Public properties
        $publicProps = array_filter($doc->properties, fn($p) => $p->visibility === 'public');
        if (!empty($publicProps)) {
            $out .= "#### Properties\n\n";
            $out .= "| Property | Type | Description |\n";
            $out .= "|----------|------|-------------|\n";
            foreach ($publicProps as $prop) {
                $readonly = $prop->isReadonly ? '(readonly) ' : '';
                $out .= "| `\${$prop->name}` | `{$prop->type}` | {$readonly}{$prop->summary} |\n";
            }
            $out .= "\n";
        }

        // Public methods
        $publicMethods = array_filter($doc->methods, fn($m) => $m->visibility === 'public');
        if (!empty($publicMethods)) {
            $out .= "#### Methods\n\n";
            foreach ($publicMethods as $method) {
                $out .= $this->renderMethodDoc($method);
            }
        }

        $out .= "---\n\n";
        return $out;
    }

    private function renderMethodDoc(MethodDoc $method): string
    {
        $modifiers = [];
        if ($method->isStatic) {
            $modifiers[] = 'static';
        }
        if ($method->isAbstract) {
            $modifiers[] = 'abstract';
        }
        if ($method->isFinal) {
            $modifiers[] = 'final';
        }
        $modifierStr = !empty($modifiers) ? implode(' ', $modifiers) . ' ' : '';

        $params = [];
        foreach ($method->parameters as $param) {
            $paramStr = "{$param->type} \${$param->name}";
            if ($param->hasDefault) {
                $paramStr .= " = {$param->default}";
            }
            $params[] = $paramStr;
        }
        $paramStr = implode(', ', $params);

        $out = "##### `{$modifierStr}{$method->name}({$paramStr}): {$method->returnType}`\n\n";

        if ($method->isApi) {
            $out .= "**API Method**\n\n";
        }

        if (!empty($method->attributes)) {
            $attrs = array_map(fn($a) => '`#[' . basename(str_replace('\\', '/', $a)) . ']`', $method->attributes);
            $out .= "**Attributes:** " . implode(', ', $attrs) . "\n\n";
        }

        if ($method->summary) {
            $out .= "{$method->summary}\n\n";
        }

        if (!empty($method->parameters)) {
            $out .= "**Parameters:**\n\n";
            foreach ($method->parameters as $param) {
                $nullable = $param->isNullable ? ' (nullable)' : '';
                $variadic = $param->isVariadic ? '...' : '';
                $out .= "- `{$variadic}\${$param->name}`: `{$param->type}`{$nullable}\n";
            }
            $out .= "\n";
        }

        if (!empty($method->throws)) {
            $out .= "**Throws:**\n\n";
            foreach ($method->throws as $throw) {
                $out .= "- `{$throw}`\n";
            }
            $out .= "\n";
        }

        return $out;
    }

    private function generateRouteDocs(): void
    {
        $output = "# API Routes Reference\n\n";
        $output .= "This document lists all API routes defined via `#[Route]` attributes.\n\n";
        $output .= "| Route Pattern | Handler Class | Method |\n";
        $output .= "|---------------|---------------|--------|\n";

        ksort($this->routes);
        foreach ($this->routes as $pattern => $info) {
            $class = str_replace('FediE2EE\\PKDServer\\', '', $info['class']);
            // Ensure pattern has exactly one leading slash
            $displayPattern = '/' . ltrim($pattern, '/');
            $filepath = str_replace('\\', '/', $class) . '.php';
            $output .= "| `{$displayPattern}` | [`{$class}`](../../src/{$filepath}) | `{$info['method']}` |\n";
        }

        $output .= "\n## Route Details\n\n";
        $output .= "Routes are configured in `config/routes.php` using League\\Route.\n";
        $output .= "The `#[Route]` attribute on handler methods is used for documentation purposes.\n";

        file_put_contents(self::OUTPUT_DIR . '/routes.md', $output);
    }

    private function generateConfigDocs(): void
    {
        $output = "# Configuration Reference\n\n";
        $output .= "This document describes configuration files in the `config/` directory.\n\n";
        $output .= "## Configuration Files\n\n";
        $output .= "| File | Purpose |\n";
        $output .= "|------|--------|\n";

        ksort($this->configFiles);
        foreach ($this->configFiles as $path => $description) {
            $output .= "| [`{$path}`](../../{$path}) | {$description} |\n";
        }

        $output .= "\n## Local Configuration\n\n";
        $output .= "Local configuration overrides are stored in `config/local/`. ";
        $output .= "These files are not tracked in version control and allow deployment-specific settings.\n\n";
        $output .= "To create local configuration, copy the base config file:\n\n";
        $output .= "```bash\ncp config/params.php config/local/params.php\n```\n\n";
        $output .= "Then modify the local copy as needed.\n";

        file_put_contents(self::OUTPUT_DIR . '/configuration.md', $output);
    }

    private function generateCmdDocs(): void
    {
        $output = "# Command-Line Scripts Reference\n\n";
        $output .= "This document describes CLI scripts in the `cmd/` directory.\n\n";
        $output .= "## Available Scripts\n\n";
        $output .= "| Script | Purpose |\n";
        $output .= "|--------|--------|\n";

        ksort($this->cmdScripts);
        foreach ($this->cmdScripts as $path => $description) {
            $output .= "| [`{$path}`](../../{$path}) | {$description} |\n";
        }

        $output .= "\n## Usage\n\n";
        $output .= "Run scripts from the project root:\n\n";
        $output .= "```bash\nphp cmd/init.php\n```\n\n";
        $output .= "### Initialization\n\n";
        $output .= "For new deployments, run `cmd/init.php` which will:\n";
        $output .= "1. Generate local configuration files\n";
        $output .= "2. Initialize the database schema\n\n";
        $output .= "### Scheduled Tasks\n\n";
        $output .= "The `cmd/scheduled-tasks.php` script should be run via cron every minute:\n\n";
        $output .= "```cron\n* * * * * /usr/bin/php /path/to/pkd-server/cmd/scheduled-tasks.php\n```\n\n";
        $output .= "This handles:\n";
        $output .= "- ActivityStream queue processing\n";
        $output .= "- Witness co-signing (runs daily)\n";

        file_put_contents(self::OUTPUT_DIR . '/cli.md', $output);
    }

    private function generateIndex(): void
    {
        $output = "# Technical Reference\n\n";
        $output .= "This section contains auto-generated technical reference documentation ";
        $output .= "for the PKD Server codebase.\n\n";
        $output .= "## Contents\n\n";
        $output .= "- [Classes Reference](classes.md) - All classes, interfaces, and traits\n";
        $output .= "- [API Routes Reference](routes.md) - HTTP API endpoints\n";
        $output .= "- [Configuration Reference](configuration.md) - Configuration files\n";
        $output .= "- [CLI Scripts Reference](cli.md) - Command-line tools\n\n";
        $output .= "## Regenerating Documentation\n\n";
        $output .= "To regenerate this documentation from source:\n\n";
        $output .= "```bash\nphp docs/reference/generate.php\n```\n";

        file_put_contents(self::OUTPUT_DIR . '/README.md', $output);
    }
}

/**
 * Data class for class documentation.
 */
final readonly class ClassDoc
{
    /**
     * @param MethodDoc[] $methods
     * @param PropertyDoc[] $properties
     * @param string[] $traits
     * @param string[] $interfaces
     */
    public function __construct(
        public string $name,
        public string $shortName,
        public string $filepath,
        public string $type,
        public string $summary,
        public string $description,
        public array $methods,
        public array $properties,
        public array $traits,
        public array $interfaces,
        public ?string $parent,
        public bool $isAbstract,
        public bool $isFinal,
    ) {}
}

/**
 * Data class for method documentation.
 */
final readonly class MethodDoc
{
    /**
     * @param ParameterDoc[] $parameters
     * @param string[] $throws
     * @param string[] $attributes
     */
    public function __construct(
        public string $name,
        public string $summary,
        public string $description,
        public array $parameters,
        public string $returnType,
        public array $throws,
        public bool $isStatic,
        public bool $isAbstract,
        public bool $isFinal,
        public string $visibility,
        public array $attributes,
        public bool $isApi,
    ) {}
}

/**
 * Data class for parameter documentation.
 */
final readonly class ParameterDoc
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $hasDefault,
        public ?string $default,
        public bool $isVariadic,
        public bool $isNullable,
    ) {}
}

/**
 * Data class for property documentation.
 */
final readonly class PropertyDoc
{
    public function __construct(
        public string $name,
        public string $type,
        public string $summary,
        public bool $hasDefault,
        public ?string $default,
        public bool $isReadonly,
        public string $visibility,
    ) {}
}

// Run the generator
(new DocGenerator())->run();
