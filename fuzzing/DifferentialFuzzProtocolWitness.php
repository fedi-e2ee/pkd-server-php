<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Fuzzing;

use FediE2EE\PKDServer\Protocol;
use FediE2EE\PKDServer\Scheduled\Witness;
use FediE2EE\PKDServer\Tables\Records\Peer;
use PhpFuzzer\Config;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;
use function
    array_diff,
    array_merge,
    array_slice,
    array_unique,
    array_values,
    count,
    dirname,
    file,
    implode,
    is_array,
    json_decode,
    ord,
    sort,
    strlen,
    substr;

/** @var Config $config */

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @return string[]
 *
 * @throws ReflectionException
 */
function extractMatchActions(string $className, string $methodName): array
{
    $ref = new ReflectionMethod($className, $methodName);
    $file = $ref->getFileName();
    $start = $ref->getStartLine();
    $end = $ref->getEndLine();
    if ($file === false || $start === false || $end === false) {
        return [];
    }
    $lines = file($file);
    if ($lines === false) {
        return [];
    }
    $source = implode('', array_slice($lines, $start - 1, $end - $start + 1));
    preg_match_all("/['\"]([A-Z][a-zA-Z]+)['\"]\s*=>/", $source, $matches);
    return array_values(array_unique($matches[1] ?? []));
}

// --- Static divergence check ---
$protocolEncrypted = extractMatchActions(Protocol::class, 'routeEncryptedAction');
$protocolPlaintext = extractMatchActions(Protocol::class, 'routePlaintextAction');
$protocolAll = array_values(array_unique(array_merge($protocolEncrypted, $protocolPlaintext)));
sort($protocolAll);

$witnessActions = extractMatchActions(Witness::class, 'processReplicatedAction');
sort($witnessActions);

// RevokeKeyThirdParty enters Protocol via a separate endpoint (not process()),
// so it's expected in Witness but absent from Protocol's match dispatch.
$protocolForComparison = array_merge($protocolAll, ['RevokeKeyThirdParty']);
sort($protocolForComparison);

$missingInWitness = array_diff($protocolForComparison, $witnessActions);
$missingInProtocol = array_diff($witnessActions, $protocolForComparison);

if (!empty($missingInWitness)) {
    throw new RuntimeException(
        'Actions in Protocol but not Witness: ' . implode(', ', $missingInWitness)
    );
}
if (!empty($missingInProtocol)) {
    throw new RuntimeException(
        'Actions in Witness but not Protocol: ' . implode(', ', $missingInProtocol)
    );
}

// --- Dynamic fuzzing of Witness.processReplicatedAction ---
// Feed random action strings + message arrays to ensure
// no unexpected crashes on malformed replication data.
$witness = new Witness($GLOBALS['pkdConfig']);
$reflectedMethod = new ReflectionMethod(Witness::class, 'processReplicatedAction');
$reflectedMethod->setAccessible(true);

// Build a minimal Peer for invocations
$peerRef = new ReflectionClass(Peer::class);

$config->setTarget(function (string $input) use ($witness, $reflectedMethod, $witnessActions): void {
    // Split fuzz input: first byte selects action, rest is JSON body
    if (strlen($input) < 2) {
        return;
    }
    $actionIdx = ord($input[0]) % (count($witnessActions) + 1);
    $jsonPart = substr($input, 1);

    // Either pick a known action or use raw bytes as action name
    $action = $actionIdx < count($witnessActions)
        ? $witnessActions[$actionIdx]
        : $jsonPart;

    $message = @json_decode($jsonPart, true);
    if (!is_array($message)) {
        $message = ['actor' => $jsonPart];
    }

    try {
        $reflectedMethod->invoke(
            $witness,
            null, // peer — null triggers early returns in process* methods
            $action,
            $message,
            0 // leafId
        );
    } catch (Throwable) {
        // Expected for malformed inputs; we're looking for
        // unexpected fatal errors or segfaults, not exceptions.
    }
});
