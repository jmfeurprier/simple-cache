Simple cache package
====================

Allows caching of data (with Memcached, file-system, etc).

## Usage

### Initialization

```php
<?php

use Jmf\Cache\CacheClient;
use Jmf\Cache\Storage\FileSystemStorage;
use Jmf\Cache\Storage\MemcachedStorage;
use Jmf\Cache\Storage\NullStorage;
use Jmf\Cache\Storage\VolatileStorage;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

/* @var ClockInterface $clock */
/* @var LoggerInterface $logger */

// Memcached
$storage = MemcachedStorage::createFromCredentials('123.45.67.89');
$cache   = new CacheClient($storage, $clock, $logger);

// Volatile storage
$storage = new VolatileStorage();
$cache   = new CacheClient($storage, $clock, $logger);

// File-system storage
$storage = new FileSystemStorage('/tmp/cache');
$cache   = new CacheClient($storage, $clock, $logger);

// Null storage (caches nothing)
$storage = new NullStorage();
$cache   = new CacheClient($storage, $clock, $logger);
```

### Storing and retrieving data

```php
<?php

$objectToStore = new \stdClass();
$objectToStore->bar = 'baz';

$cache->set('foo', $objectToStore);

// ...

$object = $cache->get('foo');
```
