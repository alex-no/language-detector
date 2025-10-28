<?php
namespace LanguageDetector\Core\Contracts;

/**
 * Interface for framework request adapter
 */
interface RequestInterface
{
    /**
     * Whether the application runs in console mode.
     */
    public function isConsole(): bool;

    /**
     * Get a GET parameter.
     */
    public function get(string $name);

    /**
     * Get a POST parameter.
     */
    public function post(string $name);

    /**
     * Whether a given HTTP header exists.
     */
    public function hasHeader(string $name): bool;

    /**
     * Get value of a given HTTP header.
     */
    public function getHeader(string $name);

    /**
     * Whether a given cookie exists.
     */
    public function hasCookie(string $name): bool;

    /**
     * Get a cookie value.
     */
    public function getCookie(string $name);

    /**
     * Whether session is available.
     */
    public function hasSession(): bool;

    /**
     * Get a session value.
     */
    public function getSession(string $name);

    /**
     * Set a session value.
     */
    public function setSession(string $name, $value): void;
}
