<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Adapter for Illuminate\Http\Request -> RequestInterface
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Illuminate\Http\Request;
use LanguageDetector\Domain\Contracts\RequestInterface;

class LaravelRequestAdapter implements RequestInterface
{
    /**
     * Constructor
     * @param Request $request Laravel HTTP request instance
     */
    public function __construct(
        private Request $request
    ) {}

    /**
     * Determine if running in console mode.
     * @return bool
     */
    public function isConsole(): bool
    {
        try {
            return app()->runningInConsole();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get a query parameter from GET request.
     * @param string $name Parameter name
     * @return mixed|null Parameter value or null if not present
     */
    public function get(string $name): mixed
    {
        return $this->request->query($name, null);
    }

    /**
     * Get a parameter from POST request.
     * @param string $name Parameter name
     * @return mixed|null Parameter value or null if not present
     */
    public function post(string $name): mixed
    {
        return $this->request->post($name, null);
    }

    /**
     * Determine if a header is present.
     * @param string $name Header name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return $this->request->headers->has($name);
    }

    /**
     * Get a header value.
     * @param string $name Header name
     * @return mixed|null Header value or null if not present
     */
    public function getHeader(string $name): mixed
    {
        return $this->request->headers->get($name);
    }

    /**
     * Determine if a cookie is present.
     * @param string $name Cookie name
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return $this->request->cookies->has($name);
    }

    /**
     * Get a cookie value.
     * @param string $name Cookie name
     * @return mixed|null Cookie value or null if not present
     */
    public function getCookie(string $name): mixed
    {
        return $this->request->cookie($name, null);
    }

    /**
     * Determine if session is available.
     * @return bool
     */
    public function hasSession(): bool
    {
        try {
            return $this->request->hasSession() && $this->request->session()->isStarted();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get a session value.
     * @param string $name Session key name
     * @return mixed|null Session value or null if not present
     */
    public function getSession(string $name): mixed
    {
        try {
            return $this->request->session()->get($name, null);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Set a session value.
     * @param string $name Session key name
     * @param mixed $value Value to set
     * @return void
     */
    public function setSession(string $name, $value): void
    {
        try {
            $this->request->session()->put($name, $value);
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Get the request path.
     * @return string|null Request path or null on error
     */
    public function getPath(): ?string
    {
        try {
            $path = $this->request->path(); // returns '' for root
            $normalized = trim((string)$path, "/ \t\n\r\0\x0B");
            return $normalized !== '' ? $normalized : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
