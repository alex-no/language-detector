<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * SimpleCache adapter wrapping Laravel cache repository.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Psr\SimpleCache\CacheInterface;
use Illuminate\Contracts\Cache\Repository as LaravelCache;

class LaravelCacheAdapter implements CacheInterface
{
    /**
     * Constructor
     * @param LaravelCache $cache Laravel cache repository
     */
    public function __construct(
        private LaravelCache $cache
    ) {}

    /**
     * @inheritDoc
     */
    public function get($key, $default = null): mixed
    {
        $value = $this->cache->get($key, $default);
        return $value === null ? $default : $value;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        try {
            if (is_int($ttl)) {
                return $this->cache->put($key, $value, $ttl);
            }
            return $this->cache->forever($key, $value);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        try {
            return $this->cache->forget($key);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        try {
            return $this->cache->flush();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v, $ttl);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $k) {
            $this->delete($k);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        try {
            return $this->cache->has($key);
        } catch (\Throwable) {
            return false;
        }
    }
}
