<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TutuRu\Metrics\StatsdExporterClientInterface;

class RequestTimingMiddleware implements MiddlewareInterface
{
    /** @var StatsdExporterClientInterface */
    private $statsdExporterClient;

    /** @var RequestMetricsCollector */
    private $requestMetricCollector;

    /** @var int */
    private $errorDefaultStatusCode = 400;


    public function __construct(StatsdExporterClientInterface $statsdExporterClient, ?float $startTime = null)
    {
        $this->statsdExporterClient = $statsdExporterClient;
        $this->requestMetricCollector = new RequestMetricsCollector($startTime);
    }


    public function setErrorDefaultStatusCode(int $statusCode)
    {
        $this->errorDefaultStatusCode = $statusCode;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = null;
        try {
            $this->requestMetricCollector->startTiming();
            $this->requestMetricCollector->setRequest($request);
            $response = $handler->handle($request);
        } finally {
            $code = !is_null($response) ? $response->getStatusCode() : $this->errorDefaultStatusCode;
            $this->requestMetricCollector->setStatusCode($code);
            $this->requestMetricCollector->endTiming();
            $this->requestMetricCollector->sendToStatsdExporter($this->statsdExporterClient);
        }
        return $response;
    }
}
