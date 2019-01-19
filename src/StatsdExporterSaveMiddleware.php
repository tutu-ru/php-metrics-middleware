<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TutuRu\Metrics\StatsdExporterClientInterface;

class StatsdExporterSaveMiddleware implements MiddlewareInterface
{
    /** @var StatsdExporterClientInterface */
    private $statsdExporterClient;


    public function __construct(StatsdExporterClientInterface $statsdExporterClient)
    {
        $this->statsdExporterClient = $statsdExporterClient;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } finally {
            $this->statsdExporterClient->save();
        }
    }
}
