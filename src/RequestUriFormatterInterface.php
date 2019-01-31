<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use Psr\Http\Message\UriInterface;

interface RequestUriFormatterInterface
{
    public function format(UriInterface $uri): string;
}
