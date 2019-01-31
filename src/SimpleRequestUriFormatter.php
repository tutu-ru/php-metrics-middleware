<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use Psr\Http\Message\UriInterface;

class SimpleRequestUriFormatter implements RequestUriFormatterInterface
{
    public function format(UriInterface $uri): string
    {
        // /v1/search/export/ --> v1_search_export
        $result = str_replace('/', '_', preg_replace('#(^/|/$)#', '', $uri->getPath()));
        // v1_carrier_1234 --> v1_carrier
        $result = preg_replace('/_\d+/', '', $result);

        return $result;
    }
}
