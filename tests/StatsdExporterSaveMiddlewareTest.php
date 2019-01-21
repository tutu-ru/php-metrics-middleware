<?php
declare(strict_types=1);

namespace TutuRu\Tests\MetricsMiddleware;

use Middlewares\Utils\Factory;
use TutuRu\MetricsMiddleware\StatsdExporterSaveMiddleware;

class StatsdExporterSaveMiddlewareTest extends BaseTest
{
    public function testSend()
    {
        // клиент создается в момент отправке, тест базируется на этом факте
        $this->assertNull($this->statsdExporterClient->getLastCreatedConnection());

        $middleware = new StatsdExporterSaveMiddleware($this->statsdExporterClient);
        $this->processRequest(Factory::createServerRequest('GET', '/'), 200, $middleware);

        $this->assertNotNull($this->statsdExporterClient->getLastCreatedConnection());
    }


    public function testSendOnException()
    {
        // клиент создается в момент отправке, тест базируется на этом факте
        $this->assertNull($this->statsdExporterClient->getLastCreatedConnection());

        $middleware = new StatsdExporterSaveMiddleware($this->statsdExporterClient);

        try {
            $this->processRequest(Factory::createServerRequest('GET', '/'), 200, $middleware, true);
        } catch (\Throwable $e) {
        }

        $this->assertNotNull($this->statsdExporterClient->getLastCreatedConnection());
    }
}
