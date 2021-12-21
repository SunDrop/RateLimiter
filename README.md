# Rate Limiters
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
    header('HTTP/1.1 429 Too Many Requests');
    exit '<!doctype html><html><body><h1>429 Too Many Requests</h1><p>You seem to be doing a lot of requests. You\'re now cooling down.</p></body></html>';
}
```

## LeakyBucketLimiter

### Теория
Leaky Bucket — это алгоритм, который обеспечивает наиболее простой, интуитивно понятный подход к ограничению скорости обработки при помощи очереди, которую можно представить в виде «ведра», содержащего запросы. Когда запрос получен, он добавляется в конец очереди. Через равные промежутки времени первый элемент в очереди обрабатывается. Это также известно как очередь FIFO. Если очередь заполнена, то дополнительные запросы отбрасываются (или “утекают”).

Преимущество данного алгоритма состоит в том, что он сглаживает всплески и обрабатывает запросы примерно с одной скоростью, его легко внедрить на одном сервере или балансировщике нагрузки, он эффективен по использованию памяти, так как размер очереди для каждого пользователя ограничен.

Однако при резком увеличении трафика очередь может заполниться старыми запросами и лишить систему возможности обрабатывать более свежие запросы. Также он не дает гарантии, что запросы будут обработаны за какое-то фиксированное время. Кроме того, если для обеспечения отказоустойчивости или увеличения пропускной способности вы загружаете балансировщики, то вы должны реализовать политику координации и обеспечения глобального ограничения между ними.

![Leaky Bucket Limiter](https://raw.github.com/SunDrop/RateLimiter/master/doc/leaky-bucket.gif)

### Пример использования

```php
use Limiter\LeakyBucketLimiter;
use Limiter\LimiterException;
use Storage\MemoryStorage;

try {
    $bucket = new LeakyBucketLimiter(
        'login', // ID
         3, // Capacity (Limit)
         1 // leak
    );
    $bucket->fill();
    // Check if it's full
    if ($bucket->isFull()) {
        throw new LimiterException();
    }
    // ... do some logic ...
} catch (LimiterException $e) {
    http_response_code(429);
    header('HTTP/1.1 429 Too Many Requests');
    exit '<!doctype html><html><body><h1>429 Too Many Requests</h1><p>You seem to be doing a lot of requests. You\'re now cooling down.</p></body></html>';
}
```
