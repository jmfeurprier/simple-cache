<?php

namespace Jmf\SimpleCache;

use DateTimeImmutable;
use Jmf\SimpleCache\Storage\CacheEntry;
use Jmf\SimpleCache\Storage\StorageInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

class CacheTest extends TestCase
{
    private StorageInterface & MockObject $storage;

    private ClockInterface & MockObject $clock;

    private LoggerInterface & MockObject $logger;

    private Cache $cache;

    #[Override]
    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->clock   = $this->createMock(ClockInterface::class);
        $this->logger  = $this->createMock(LoggerInterface::class);

        $this->cache = new Cache(
            $this->storage,
            $this->clock,
            $this->logger,
        );
    }

    public function testStoreAndFetch(): void
    {
        $key     = 'foo';
        $content = 'bar';
        $now     = new DateTimeImmutable();

        $cacheEntry = new CacheEntry($key, $content, $now->getTimestamp(), null);

        $this->storage->expects($this->once())->method('set');
        $this->storage->expects($this->once())->method('get')->with($key)->willReturn($cacheEntry);

        $this->clock->expects($this->atLeastOnce())->method('now')->willReturn($now);

        $this->cache->set($key, $content);

        $result = $this->cache->get($key);

        $this->assertSame($content, $result);
    }
}
