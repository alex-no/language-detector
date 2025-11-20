<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Adapter for Symfony HttpFoundation Request
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Symfony\Component\HttpFoundation\Request;
use LanguageDetector\Domain\Contracts\RequestInterface;

class SymfonyRequestAdapter implements RequestInterface
{
    /**
     * Constructor.
     * @param Request $request Symfony HTTP request.
     */
    public function __construct(
        private Request $request
    ) {}

    /**
     * Check if the current execution is in console.
     * @return bool True if in console, false otherwise.
     */
    public function isConsole(): bool
    {
        try {
            return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get query parameter by name.
     * @param string $name Parameter name.
     * @return mixed Parameter value or null if not found.
     */
    public function get(string $name): mixed
    {
        return $this->request->query->get($name, null);
    }

    /**
     * Get POST parameter by name.
     * @param string $name Parameter name.
     * @return mixed Parameter value or null if not found.
     */
    public function post(string $name): mixed
    {
        return $this->request->request->get($name, null);
    }

    /**
     * Check if header exists.
     * @param string $name Header name.
     * @return bool True if header exists, false otherwise.
     */
    public function hasHeader(string $name): bool
    {
        return $this->request->headers->has($name);
    }

    /**
     * Get header value by name.
     * @param string $name Header name.
     * @return mixed Header value or null if not found.
     */
    public function getHeader(string $name): mixed
    {
        return $this->request->headers->get($name);
    }

    /**
     * Check if cookie exists.
     * @param string $name Cookie name.
     * @return bool True if cookie exists, false otherwise.
     */
    public function hasCookie(string $name): bool
    {
        return $this->request->cookies->has($name);
    }

    /**
     * Get cookie value by name.
     * @param string $name Cookie name.
     * @return mixed Cookie value or null if not found.
     */
    public function getCookie(string $name): mixed
    {
        return $this->request->cookies->get($name, null);
    }

    /**
     * Check if session exists.
     * @return bool True if session exists, false otherwise.
     */
    public function hasSession(): bool
    {
        try {
            $s = $this->request->getSession();
            return $s !== null && $s->isStarted();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get session value by name.
     * @param string $name Session key name.
     * @return mixed Session value or null if not found.
     */
    public function getSession(string $name): mixed
    {
        try {
            $s = $this->request->getSession();
            return $s ? $s->get($name, null) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Set session value by name.
     * @param string $name Session key name.
     * @param mixed $value Value to set.
     * @return void
     */
    public function setSession(string $name, $value): void
    {
        try {
            $s = $this->request->getSession();
            if ($s) {
                $s->set($name, $value);
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Get the request path.
     * @return string|null The request path or null if not available.
     */
    public function getPath(): ?string
    {
        try {
            $path = $this->request->getPathInfo(); // '/en/some'
            $normalized = trim((string)$path, "/ \t\n\r\0\x0B");
            return $normalized !== '' ? $normalized : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
