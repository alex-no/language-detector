<?php
namespace LanguageDetector\Core\Contracts;

/**
 * Simple cache abstraction for the detector core.
 * Compatible with PSR-16 style get/set.
 */
interface CacheInterface
{
    /**
     * Get cached value.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key);

    /**
     * Set cached value.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl TTL in seconds
     */
    public function set(string $key, $value, $ttl = null);
}
