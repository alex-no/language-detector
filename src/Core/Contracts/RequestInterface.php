<?php
namespace LanguageDetector\Core\Contracts;
/**
 * Interface for framework request adapter
 * RequestInterface.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Core\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
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
    public function get(string $name): mixed;

    /**
     * Get a POST parameter.
     */
    public function post(string $name): mixed;

    /**
     * Whether a given HTTP header exists.
     */
    public function hasHeader(string $name): bool;

    /**
     * Get value of a given HTTP header.
     */
    public function getHeader(string $name): mixed;

    /**
     * Whether a given cookie exists.
     */
    public function hasCookie(string $name): bool;

    /**
     * Get a cookie value.
     */
    public function getCookie(string $name): mixed;

    /**
     * Whether session is available.
     */
    public function hasSession(): bool;

    /**
     * Get a session value.
     */
    public function getSession(string $name): mixed;

    /**
     * Set a session value.
     */
    public function setSession(string $name, $value): void;

    /**
     * Return request path in normalized form, without query string.
     * Example returns: '/en/api/lang-debug' or 'en/api/lang-debug' or '/api/...'
     * The method should return null for console or when path is not available.
     *
     * @return string|null
     */
    public function getPath(): ?string;
}
