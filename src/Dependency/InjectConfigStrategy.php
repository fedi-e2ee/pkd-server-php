<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Dependency;

use FediE2EE\PKDServer\Exceptions\DependencyException;
use FediE2EE\PKDServer\ServerConfig;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Route;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use function call_user_func_array, is_array, is_object, method_exists;

class InjectConfigStrategy extends ApplicationStrategy
{
    private ServerConfig $config;

    /**
     * @throws DependencyException
     */
    public function __construct()
    {
        $config = $GLOBALS['pkdConfig'];
        if (!($config instanceof ServerConfig)) {
            throw new DependencyException('config not defined globally');
        }
        $this->config = $config;
    }

    /**
     * @throws DependencyException
     */
    #[Override]
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $callable = $route->getCallable($this->getContainer());

        if (is_array($callable)) {
            // Handle [ClassName, 'method'] or [$instance, 'method']
            $controller = $callable[0];
            if (is_object($controller) && method_exists($controller, 'injectConfig')) {
                $controller->injectConfig($this->config);
            }
        } elseif (is_object($callable) && method_exists($callable, 'injectConfig')) {
            $callable->injectConfig($this->config);
        }

        // Manually invoke the callable with the request as the first arg
        // (League does this via call_user_func_array internally)
        $response = call_user_func_array($callable, [$request]);

        // Ensure it's a ResponseInterface (League's default behavior)
        if (!($response instanceof ResponseInterface)) {
            throw new DependencyException('Handler must return a ResponseInterface');
        }
        return $response;
    }
}
