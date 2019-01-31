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
    private $statsdExporterClient;

    private $startTime;

    /** @var int */
    private $errorDefaultStatusCode = 400;

    /** @var RequestUriFormatterInterface */
    private $uriFormatter;


    public function __construct(StatsdExporterClientInterface $statsdExporterClient, ?float $startTime = null)
    {
        $this->statsdExporterClient = $statsdExporterClient;
        $this->startTime = $startTime;
    }


    public function setErrorDefaultStatusCode(int $statusCode)
    {
        $this->errorDefaultStatusCode = $statusCode;
    }


    public function setUriFormatter(RequestUriFormatterInterface $uriFormatter)
    {
        $this->uriFormatter = $uriFormatter;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = null;
        try {
            $uriFormatter = $this->uriFormatter ?? new SimpleRequestUriFormatter();
            $requestMetricCollector = new RequestMetricsCollector(
                $uriFormatter->format($request->getUri()),
                strtolower($request->getMethod()),
                $this->startTime
            );
            $requestMetricCollector->startTiming();
            $response = $handler->handle($request);
        } finally {
            $code = !is_null($response) ? $response->getStatusCode() : $this->errorDefaultStatusCode;
            $requestMetricCollector->setStatusCode($code);
            $requestMetricCollector->endTiming();
            $requestMetricCollector->sendToStatsdExporter($this->statsdExporterClient);
        }
        return $response;
    }
}
