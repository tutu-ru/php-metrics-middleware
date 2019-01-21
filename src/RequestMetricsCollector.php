<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use Psr\Http\Message\ServerRequestInterface;
use TutuRu\Metrics\MetricCollector;

class RequestMetricsCollector extends MetricCollector
{
    /** @var float */
    private $realStartTime;

    private $uri;
    private $method;
    private $statusCode;

    public function __construct(float $realStartTime = null)
    {
        $this->realStartTime = $realStartTime;
    }

    public function startTiming(?float $timeSeconds = null): void
    {
        parent::startTiming($timeSeconds ?? $this->realStartTime);
    }


    public function setRequest(ServerRequestInterface $request)
    {
        // /v1/search/export/ --> v1_search_export
        $this->uri = str_replace('/', '_', preg_replace('#(^/|/$)#', '', $request->getUri()->getPath()));
        // v1_carrier_1234 --> v1_carrier
        $this->uri = preg_replace('/_\d+/', '', $this->uri);
        $this->method = strtolower($request->getMethod());
    }


    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }


    protected function getTimersMetricName(): string
    {
        return 'http_rest_service_api_request_duration';
    }


    protected function getTimersMetricTags(): array
    {
        return [
            'uri'      => $this->uri ?: '_',
            'method'   => $this->method,
            'response' => $this->statusCode ?? 'unknown'
        ];
    }
}
