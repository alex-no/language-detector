<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Adapter that tries to wrap common Symfony cache components into PSR-16 SimpleCache.
 *
 * Accepts either Symfony\Contracts\Cache\CacheInterface or Psr\Cache\CacheItemPoolInterface or null.
 * Implementation is best-effort for methods used by the detector.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Psr\SimpleCache\CacheInterface;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCache;
use Psr\Cache\CacheItemPoolInterface;

class SymfonyCacheAdapter implements CacheInterface
{
    private ?SymfonyCache $symfonyCache = null;
    private ?CacheItemPoolInterface $pool = null;

    /**
     * Constructor.
     * @param SymfonyCache|CacheItemPoolInterface|null $cache Cache instance.
     */
    public function __construct($cache)
    {
        if ($cache instanceof SymfonyCache) {
            $this->symfonyCache = $cache;
        } elseif ($cache instanceof CacheItemPoolInterface) {
            $this->pool = $cache;
        }
    }

    /**
     * Get value by key.
     * @param string $key Cache key.
     * @param mixed $default Default value if not found.
     * @return mixed Value or default.
     */
    public function get($key, $default = null): mixed
    {
        try {
            if ($this->symfonyCache) {
                // symfony cache contract: get($key, callable)
                // cannot read without callback; attempt to call with noop that returns null
                return $this->symfonyCache->get($key, function () use ($default) {
                    return $default;
                });
            }
            if ($this->pool) {
                $item = $this->pool->getItem($key);
                if ($item->isHit()) {
                    return $item->get();
                }
                return $default;
            }
        } catch (\Throwable) {}
        return $default;
    }

    /**
     * Set value by key.
     * @param string $key Cache key.
     * @param mixed $value Value to set.
     * @param null|int|\DateInterval $ttl Time to live in seconds or DateInterval or null.
     * @return bool True on success, false on failure.
     */
    public function set($key, $value, $ttl = null): bool
    {
        try {
            if ($this->symfonyCache) {
                // symfony cache contract does not offer direct set; use get with callable that returns value
                $this->symfonyCache->get($key, function () use ($value) {
                    return $value;
                });
                return true;
            }
            if ($this->pool) {
                $item = $this->pool->getItem($key);
                $item->set($value);
                if (is_int($ttl)) {
                    $item->expiresAfter($ttl);
                }
                return $this->pool->save($item);
            }
        } catch (\Throwable) {}
        return false;
    }

    /**
     * Delete value by key.
     * @param string $key Cache key.
     * @return bool True on success, false on failure.
     */
    public function delete($key): bool
    {
        try {
            if ($this->pool) {
                return $this->pool->deleteItem($key);
            }
            // symfony cache lacks delete in contract used — best-effort via get with null
        } catch (\Throwable) {}
        return false;
    }

    /**
     * Clear all cache.
     * @return bool True on success, false on failure.
     */
    public function clear(): bool
    {
        try {
            if ($this->pool) {
                return $this->pool->clear();
            }
        } catch (\Throwable) {}
        return false;
    }

    /**
     * Get multiple values by keys.
     * @param iterable $keys Cache keys.
     * @param mixed $default Default value if not found.
     * @return iterable Key-value pairs.
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $this->get($k, $default);
        }
        return $out;
    }

    /**
     * Set multiple values.
     * @param iterable $values Key-value pairs.
     * @param null|int|\DateInterval $ttl Time to live in seconds or DateInterval or null.
     * @return bool True on success, false on failure.
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v, $ttl);
        }
        return true;
    }

    /**
     * Delete multiple values by keys.
     * @param iterable $keys Cache keys.
     * @return bool True on success, false on failure.
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $k) {
            $this->delete($k);
        }
        return true;
    }

    /**
     * Check if key exists.
     * @param string $key Cache key.
     * @return bool True if exists, false otherwise.
     */
    public function has($key): bool
    {
        try {
            if ($this->pool) {
                return $this->pool->hasItem($key);
            }
        } catch (\Throwable) {}
        return false;
    }
}
