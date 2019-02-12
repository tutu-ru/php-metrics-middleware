<?php
declare(strict_types=1);

namespace TutuRu\Tests\MetricsMiddleware;

use Middlewares\Utils\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TutuRu\Config\JsonConfig\JsonConfig;
use TutuRu\Tests\Metrics\MemoryStatsdExporter\MemoryStatsdExporterClient;

abstract class BaseTest extends TestCase
{
    /** @var JsonConfig */
    protected $config;

    /** @var MemoryStatsdExporterClient */
    protected $statsdExporterClient;


    public function setUp()
    {
        parent::setUp();
        $this->statsdExporterClient = new MemoryStatsdExporterClient("test");
    }


    protected function processRequest(
        ServerRequestInterface $request,
        int $responseCode,
        MiddlewareInterface $middleware,
        bool $raiseException = false
    ): ResponseInterface {
        return Dispatcher::run(
            [
                $middleware,
                function ($request, $next) use ($responseCode, $raiseException) {
                    /** @var ResponseInterface $response */
                    /** @var RequestHandlerInterface $next */
                    $response = $next->handle($request);
                    if ($raiseException) {
                        throw new \Exception("error in handler");
                    }
                    return $response->withStatus($responseCode, 'ok');
                }
            ],
            $request
        );
    }
}
