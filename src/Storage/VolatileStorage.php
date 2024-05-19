<?php

namespace Jmf\SimpleCache\Storage;

use Override;

class VolatileStorage implements StorageInterface
{
    /**
     * @var array<string, CacheEntry>
     */
    private array $storedEntries = [];

    #[Override]
    public function set(CacheEntry $cacheEntry): void
    {
        $this->storedEntries[$cacheEntry->getKey()] = $cacheEntry;
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
        // @todo Check expiration.

        return $this->storedEntries[$key] ?? null;
    }

    #[Override]
    public function getMultiple(iterable $keys): iterable
    {
        $entries = [];

        foreach ($keys as $key) {
            $entry = $this->get($key);

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    #[Override]
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storedEntries);
    }

    #[Override]
    public function delete(string $key): void
    {
        unset($this->storedEntries[$key]);
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
        $this->storedEntries = [];
    }
}
