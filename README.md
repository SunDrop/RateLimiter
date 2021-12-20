#Rate Limiters
## Setup project
Run docker containers
```
$ make up
```
Go into php container
```
$ make php
```
## FixedWindowLimiter

### Теория
В этом алгоритме для отслеживания запросов используется окно, равное n секундам. Обычно используются значения вроде 60 секунд (минута) или 3600 секунд (час). Каждый входящий запрос увеличивает счётчик для этого окна. Если счётчик превышает некое пороговое значение, запрос отбрасывается. Обычно окно определяется нижней границей текущего временного интервала, то есть при ширине окна в 60 секунд, запрос, пришедший в 12:00:03, попадёт в окно 12:00:00.

Преимущество этого алгоритма состоит в том, что он обеспечивает обработку более свежих запросов, не зависая на обработке старых. Однако одиночный всплеск трафика вблизи границы окна может привести к удвоению количества обработанных запросов, поскольку он разрешает запросы как для текущего, так и для следующего окна в течение короткого промежутка времени. Кроме того, если много пользователей ждут сброса счётчика окна, например, в конце часа, они могут спровоцировать рост нагрузки в этот момент из-за того, что обратятся к API одновременно.

![Fixed Window Limiter](https://raw.github.com/SunDrop/RateLimiter/master/doc/fixed-window.gif)

### Пример использования

```php
use Limiter\FixedWindowLimiter;
use Limiter\LimiterException;
use Storage\MemoryStorage;

$limiter = new FixedWindowLimiter(
    'login', // ID
     2, // Limit
     '10 seconds', // time interval
     new MemoryStorage()
    );

try {
    $limiter->makeRequest();
    // ... do some logic ...
} catch (LimiterException $e) {
    http_response_code(429);
    header('HTTP/1.0 429 Too Many Requests');
}
```
