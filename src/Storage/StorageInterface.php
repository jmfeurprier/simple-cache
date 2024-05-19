<?php

namespace Jmf\SimpleCache\Storage;

use Jmf\SimpleCache\Exception\CacheException;

interface StorageInterface
{
    /**
     * @throws CacheException
     */
    public function set(CacheEntry $cacheEntry): void;

    /**
     * @throws CacheException
     */
    public function setMultiple(CacheEntries $cacheEntries): void;

    /**
     * @throws CacheException
     */
    public function get(string $key): ?CacheEntry;

    /**
     * @param string[] $keys
     *
     * @return CacheEntry[]
     *
     * @throws CacheException
     */
    public function getMultiple(iterable $keys): iterable;

    /**
     * @throws CacheException
     */
    public function has(string $key): bool;

    /**
     * @throws CacheException
     */
    public function delete(string $key): void;

    /**
     * @param string[] $keys
     *
     * @throws CacheException
     */
    public function deleteMultiple(iterable $keys): void;

    /**
     * @throws CacheException
     */
    public function clear(): void;
}
