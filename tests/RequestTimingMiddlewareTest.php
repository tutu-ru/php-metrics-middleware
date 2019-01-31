<?php
declare(strict_types=1);

namespace TutuRu\Tests\MetricsMiddleware;

use Middlewares\Utils\Factory;
use TutuRu\MetricsMiddleware\RequestTimingMiddleware;
use TutuRu\Tests\Metrics\MemoryMetricExporter\MemoryMetric;

class RequestTimingMiddlewareTest extends BaseTest
{
    public function requestDataProvider()
    {
        return [
            [
                Factory::createServerRequest('GET', '/'),
                200,
                ['app' => 'test', 'uri' => '_', 'method' => 'get', 'response' => 200]
            ],
            [
                Factory::createServerRequest('GET', '/main'),
                200,
                ['app' => 'test', 'uri' => 'main', 'method' => 'get', 'response' => 200]
            ],
            [
                Factory::createServerRequest('GET', '/main/1'),
                200,
                ['app' => 'test', 'uri' => 'main', 'method' => 'get', 'response' => 200]
            ],
            [
                Factory::createServerRequest('POST', '/main/post'),
                200,
                ['app' => 'test', 'uri' => 'main_post', 'method' => 'post', 'response' => 200]
            ],
            [
                Factory::createServerRequest('DELETE', '/main/post'),
                404,
                ['app' => 'test', 'uri' => 'main_post', 'method' => 'delete', 'response' => 404]
            ],
            [
                Factory::createServerRequest('GET', '/main?filter=a'),
                200,
                ['app' => 'test', 'uri' => 'main', 'method' => 'get', 'response' => 200]
            ],
        ];
    }


    /**
     * @dataProvider requestDataProvider
     */
    public function testRequestProcessing($request, $responseCode, $expectedTags)
    {
        $this->processRequest($request, $responseCode, new RequestTimingMiddleware($this->statsdExporterClient));
        $this->statsdExporterClient->save();

        $this->assertCount(1, $this->statsdExporterClient->getExportedMetrics());
        /** @var MemoryMetric $metric */
        $metric = current($this->statsdExporterClient->getExportedMetrics());
        $this->assertEquals($expectedTags, $metric->getTags());
        $this->assertEquals('ms', $metric->getUnit());
        $this->assertEquals('http_rest_service_api_request_duration', $metric->getName());
        $this->assertGreaterThanOrEqual(0, $metric->getValue());
    }


    /**
     * @dataProvider requestDataProvider
     */
    public function testRequestProcessingWithCustomUriFormatter($request, $responseCode, $expectedTags)
    {
        $middleware = new RequestTimingMiddleware($this->statsdExporterClient);
        $middleware->setUriFormatter(new RequestUriFormatter());
        $this->processRequest($request, $responseCode, $middleware);
        $this->statsdExporterClient->save();

        $this->assertCount(1, $this->statsdExporterClient->getExportedMetrics());
        /** @var MemoryMetric $metric */
        $metric = current($this->statsdExporterClient->getExportedMetrics());
        $expectedTags['uri'] = strtoupper(preg_replace("/[^a-z]/", "", $expectedTags['uri'])) ?: '_';
        $this->assertEquals($expectedTags, $metric->getTags());
        $this->assertEquals('ms', $metric->getUnit());
        $this->assertEquals('http_rest_service_api_request_duration', $metric->getName());
        $this->assertGreaterThanOrEqual(0, $metric->getValue());
    }


    /**
     * @dataProvider requestDataProvider
     */
    public function testHandlerException($request, $responseCode, $expectedTags)
    {
        $responseCode = 400;

        try {
            $this->processRequest($request, 200, new RequestTimingMiddleware($this->statsdExporterClient), true);
        } catch (\Throwable $e) {
        }
        $this->statsdExporterClient->save();

        $this->assertCount(1, $this->statsdExporterClient->getExportedMetrics());
        /** @var MemoryMetric $metric */
        $metric = current($this->statsdExporterClient->getExportedMetrics());
        $this->assertEquals(array_merge($expectedTags, ['response' => $responseCode]), $metric->getTags());
        $this->assertEquals('ms', $metric->getUnit());
        $this->assertEquals('http_rest_service_api_request_duration', $metric->getName());
        $this->assertGreaterThanOrEqual(0, $metric->getValue());
    }


    /**
     * @dataProvider requestDataProvider
     */
    public function testHandlerExceptionWithCustomResponseCode($request, $responseCode, $expectedTags)
    {
        $responseCode = 500;
        $middleware = new RequestTimingMiddleware($this->statsdExporterClient);
        $middleware->setErrorDefaultStatusCode($responseCode);

        try {
            $this->processRequest($request, 200, $middleware, true);
        } catch (\Throwable $e) {
        }
        $this->statsdExporterClient->save();

        $this->assertCount(1, $this->statsdExporterClient->getExportedMetrics());
        /** @var MemoryMetric $metric */
        $metric = current($this->statsdExporterClient->getExportedMetrics());
        $this->assertEquals(array_merge($expectedTags, ['response' => $responseCode]), $metric->getTags());
        $this->assertEquals('ms', $metric->getUnit());
        $this->assertEquals('http_rest_service_api_request_duration', $metric->getName());
        $this->assertGreaterThanOrEqual(0, $metric->getValue());
    }


    public function testCustomStartTime()
    {
        $startTimeShift = 3600.;
        $middleware = new RequestTimingMiddleware($this->statsdExporterClient, microtime(true) - $startTimeShift);

        $this->processRequest(Factory::createServerRequest('GET', '/'), 200, $middleware);
        $this->statsdExporterClient->save();

        $this->assertCount(1, $this->statsdExporterClient->getExportedMetrics());
        /** @var MemoryMetric $metric */
        $metric = current($this->statsdExporterClient->getExportedMetrics());
        $this->assertGreaterThanOrEqual($startTimeShift * 1000, $metric->getValue());
    }
}
