<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use TutuRu\Metrics\MetricCollector;

class RequestMetricsCollector extends MetricCollector
{
    /** @var float */
    private $realStartTime;

    private $uri;
    private $method;
    private $statusCode;

    public function __construct(string $uri, string $method, float $realStartTime = null)
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->realStartTime = $realStartTime;
    }

    public function startTiming(?float $timeSeconds = null): void
    {
        parent::startTiming($timeSeconds ?? $this->realStartTime);
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
            'uri'      => $this->uri,
            'method'   => $this->method,
            'response' => $this->statusCode ?? 'unknown'
        ];
    }
}
