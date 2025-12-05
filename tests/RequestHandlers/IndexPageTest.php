<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers;

use FediE2EE\PKDServer\RequestHandlers\IndexPage;
use FediE2EE\PKDServer\ServerConfig;
use FediE2EE\PKDServer\Tests\HttpTestTrait;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexPage::class)]
#[UsesClass(ServerConfig::class)]
class IndexPageTest extends TestCase
{
    use HttpTestTrait;

    public function testHandle(): void
    {
        $request = $this->makeGetRequest('/');

        $handler = new IndexPage();
        $handler->injectConfig($GLOBALS['pkdConfig']);
        $response = $handler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }
}
