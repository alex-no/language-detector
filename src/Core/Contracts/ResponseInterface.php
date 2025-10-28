<?php
namespace LanguageDetector\Core\Contracts;

/**
 * Interface for framework response adapter
 */
interface ResponseInterface
{
    /**
     * Add or update a cookie.
     *
     * @param string $name
     * @param mixed $value
     * @param int $expire UNIX timestamp when the cookie expires
     */
    public function addCookie(string $name, $value, int $expire): void;
}
