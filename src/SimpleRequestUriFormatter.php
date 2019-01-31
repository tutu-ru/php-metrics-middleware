<?php
declare(strict_types=1);

namespace TutuRu\MetricsMiddleware;

use Psr\Http\Message\UriInterface;

class SimpleRequestUriFormatter implements RequestUriFormatterInterface
{
    public function format(UriInterface $uri): string
    {
        // /v1/carrier/1234 --> /v1/carrier
        $result = preg_replace('#/\d+/?$#', '', $uri->getPath());
        if ($result !== '/') {
            $result = preg_replace('#/$#', '', $result);
        }
        return $result;
    }
}
