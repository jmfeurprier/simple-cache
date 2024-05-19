<?php

namespace Jmf\SimpleCache\Storage;

use Override;

class NullStorage implements StorageInterface
{
    #[Override]
    public function set(CacheEntry $cacheEntry): void
    {
    }

    #[Override]
    public function setMultiple(CacheEntries $cacheEntries): void
    {
    }

    #[Override]
    public function get(string $key): ?CacheEntry
    {
        return null;
    }

    #[Override]
    public function getMultiple(iterable $keys): iterable
    {
        return [];
    }

    #[Override]
    public function has(string $key): bool
    {
        return false;
    }

    #[Override]
    public function delete(string $key): void
    {
    }

    #[Override]
    public function deleteMultiple(iterable $keys): void
    {
    }

    #[Override]
    public function clear(): void
    {
    }
}
