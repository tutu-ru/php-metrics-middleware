<?php
declare(strict_types=1);

namespace TutuRu\Tests\MetricsMiddleware;

use Psr\Http\Message\UriInterface;
use TutuRu\MetricsMiddleware\RequestUriFormatterInterface;

class RequestUriFormatter implements RequestUriFormatterInterface
{
    public function format(UriInterface $uri): string
    {
        return strtoupper(preg_replace("/[^\/a-z]/", "", $uri->getPath()));
    }
}
