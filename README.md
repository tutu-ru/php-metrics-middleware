# Библиотека MetricsMiddleware

PSR-15 middleware для измерения времени обработки запроса и отправки метрик.

## Подключение

```bash
composer require tutu-ru/lib-metrics-middleware
```

## RequestTimingMiddleware

Измеряет время обработки запроса.
Для максимальной точности измерения данный middleware должен выполняться одинм из первых.

```php
use TutuRu\Metrics\StatsdExporterClientFactory;
use TutuRu\MetricsMiddleware\RequestTimingMiddleware;

$statsExporterClient = StatsdExporterClientFactory::create($config);
$middleware = new RequestMetadataMiddleware($statsExporterClient);
// add to application
```

Так как до инициализации middleware в приложении могут происходить дополнительные действия предусмотрена передача произвольного времени старта:

```php
use TutuRu\Metrics\StatsdExporterClientFactory;
use TutuRu\MetricsMiddleware\RequestTimingMiddleware;

$startTime = microtime(true);

// some useful thing

$statsExporterClient = StatsdExporterClientFactory::create($config);
$middleware = new RequestMetadataMiddleware($statsExporterClient, $startTime);
// add to application
```

## StatsdExporterSaveMiddleware

Отправляет все накопленные метрики.

Должен срабатывать в самом конце обработки, после всех middleware, которые могли собирать метрики.

```php
use TutuRu\Metrics\StatsdExporterClientFactory;
use TutuRu\MetricsMiddleware\StatsdExporterSaveMiddleware;

$statsExporterClient = StatsdExporterClientFactory::create($config);
$middleware = new StatsdExporterSaveMiddleware($statsExporterClient);
// add to application
```
