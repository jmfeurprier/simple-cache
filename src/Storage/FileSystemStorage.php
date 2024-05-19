<?php

namespace Jmf\SimpleCache\Storage;

use Jmf\SimpleCache\Exception\CacheException;
use Override;

class FileSystemStorage implements StorageInterface
{
    private const string CACHE_FILE_SUFFIX = '.cache';

    public function __construct(
        private string $basePath,
    ) {
        $this->basePath = rtrim($this->basePath, '/\\') . '/';
    }

    #[Override]
    public function set(CacheEntry $cacheEntry): void
    {
        $cacheFilePath = $this->getCacheFilePath($cacheEntry->getKey());
        $packedEntry   = serialize($cacheEntry);

        if (false === file_put_contents($cacheFilePath, $packedEntry)) {
            throw new CacheException('Failed to store cache entry.');
        }
    }

    #[Override]
    public function setMultiple(CacheEntries $cacheEntries): void
    {
        foreach ($cacheEntries->getValues() as $key => $content) {
            $this->set(
                new CacheEntry(
                    $key,
                    $content,
                    $cacheEntries->getCreationTimestamp(),
                    $cacheEntries->getExpirationTimestamp(),
                )
            );
        }
    }

    #[Override]
    public function get(string $key): ?CacheEntry
    {
        $cacheFilePath = $this->getCacheFilePath($key);

        // Cache file missing?
        if (!file_exists($cacheFilePath)) {
            return null;
        }

        $fileContent = file_get_contents($cacheFilePath);

        // File read failure?
        if (false === $fileContent) {
            throw new CacheException('Failed to read cache file.');
        }

        $cacheEntry = unserialize($fileContent);

        if ($cacheEntry instanceof CacheEntry) {
            return $cacheEntry;
        }

        throw new CacheException('Failed to read cache file.');
    }

    #[Override]
    public function getMultiple(iterable $keys): iterable
    {
        $entries = [];

        foreach ($keys as $key) {
            $entry = $this->get($key);

            if (null !== $entry) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    #[Override]
    public function has(string $key): bool
    {
        $cacheFilePath = $this->getCacheFilePath($key);

        return file_exists($cacheFilePath);
    }

    #[Override]
    public function delete(string $key): void
    {
        $cacheFilePath = $this->getCacheFilePath($key);

        if (!unlink($cacheFilePath)) {
            throw new CacheException("Failed to delete cache file '{$cacheFilePath}'.");
        }
    }

    #[Override]
    public function deleteMultiple(iterable $keys): void
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    #[Override]
    public function clear(): void
    {
        $mask  = '*' . self::CACHE_FILE_SUFFIX;
        $paths = glob($this->basePath . $mask);

        if (!is_iterable($paths)) {
            throw new CacheException("Failed to delete cache files.");
        }

        foreach ($paths as $cacheFilePath) {
            if (!unlink($cacheFilePath)) {
                throw new CacheException("Failed to delete cache file '{$cacheFilePath}'.");
            }
        }
    }

    private function getCacheFilePath(string $key): string
    {
        return $this->basePath . md5($key) . self::CACHE_FILE_SUFFIX;
    }
}
