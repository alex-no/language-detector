<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Adapter to add cookie to response.
 * We only need to queue cookie; Laravel attaches queued cookies to responses.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Illuminate\Contracts\Routing\ResponseFactory;
use LanguageDetector\Domain\Contracts\ResponseInterface;

class LaravelResponseAdapter implements ResponseInterface
{
    /**
     * Constructor
     * @param ResponseFactory $responseFactory Laravel response factory
     */
    public function __construct(
        private ResponseFactory $responseFactory
    ) {}

    /**
     * Add a cookie to the response.
     * @param string $name Cookie name
     * @param mixed $value Cookie value
     * @param int $expire Expiration time as Unix timestamp
     * @return void
     */
    public function addCookie(string $name, $value, int $expire): void
    {
        try {
            // expiration: unix timestamp -> minutes from now for cookie helper
            $minutes = (int)ceil(($expire - time()) / 60);
            // queue cookie via global cookie helper if available
            if (function_exists('cookie')) {
                cookie()->queue(cookie($name, (string)$value, $minutes));
                return;
            }
            // fallback: try to create a response and attach cookie (best-effort)
            $resp = $this->responseFactory->make('');
            $resp->withCookie(cookie($name, (string)$value, $minutes));
        } catch (\Throwable) {
            // ignore
        }
    }
}
