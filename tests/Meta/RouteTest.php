<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\Meta;

use FediE2EE\PKDServer\Meta\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Route::class)]
class RouteTest extends TestCase
{
    public function testRoute(): void
    {
        $route = new Route('/foo');
        $this->assertSame('/foo', $route->uriPattern);
    }
}
