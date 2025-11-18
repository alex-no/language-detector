<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelRequestAdapter.php
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use LanguageDetector\Domain\Language\Contracts\RequestInterface as CoreRequestInterface;
use Illuminate\Http\Request as LaravelRequest;

class LaravelRequestAdapter implements CoreRequestInterface
{
    /**
     * @param LaravelRequest $request
     */
    public function __construct(
        private LaravelRequest $request,
    ) {}

    /**
     * @inheritDoc
     */
    public function isConsole(): bool
    {
        return app()->runningInConsole();
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): mixed
    {
        return $this->request->query($name, null);
    }

    /**
     * @inheritDoc
     */
    public function post(string $name): mixed
    {
        return $this->request->post($name, null);
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return $this->request->headers->has($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): mixed
    {
        // return header value (string or array)
        $value = $this->request->header($name);
        return $value !== null ? $value : null;
    }

    /**
     * @inheritDoc
     */
    public function hasCookie(string $name): bool
    {
        return $this->request->cookies->has($name);
    }

    /**
     * @inheritDoc
     */
    public function getCookie(string $name): mixed
    {
        return $this->request->cookie($name, null);
    }

    /**
     * @inheritDoc
     */
    public function hasSession(): bool
    {
        return $this->request->hasSession() && $this->request->session() !== null;
    }

    /**
     * @inheritDoc
     */
    public function getSession(string $name): mixed
    {
        if (! $this->hasSession()) {
            return null;
        }
        return $this->request->session()->get($name, null);
    }
}
