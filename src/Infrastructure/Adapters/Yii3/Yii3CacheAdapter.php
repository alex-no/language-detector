<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3CacheAdapter.php
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii3
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Cache\CacheInterface as YiiCacheInterface;

class Yii3CacheAdapter implements CacheInterface
{
    /**
     * Yii3CacheAdapter constructor.
     * @param YiiCacheInterface $cache
     */
    public function __construct(
        private YiiCacheInterface $cache
    ) {}

    /**
     * @inheritDoc
     */
    public function get($key, $default = null): mixed
    {
        try {
            $value = $this->cache->get($key);
            return $value !== null ? $value : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        try {
            if ($ttl === null) {
                return $this->cache->set($key, $value);
            }
            return $this->cache->set($key, $value, $ttl);
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
            return $this->cache->remove($key);
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
            return $this->cache->clear();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            foreach ($values as $key => $value) {
                if (!$this->set($key, $value, $ttl)) {
                    return false;
                }
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        try {
            foreach ($keys as $key) {
                if (!$this->delete($key)) {
                    return false;
                }
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
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
