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
