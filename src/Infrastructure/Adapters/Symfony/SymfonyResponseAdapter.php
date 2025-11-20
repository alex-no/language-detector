<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Adapter to add cookie to Symfony Response cookies bag.
 * We attempt to add cookie to current response if available, otherwise to headers (best-effort).
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use LanguageDetector\Domain\Contracts\ResponseInterface;

class SymfonyResponseAdapter implements ResponseInterface
{
    /**
     * Constructor.
     * @param Response|null $response Symfony Response object (optional).
     */
    public function __construct(
        private ?Response $response = null
    ) {}

    /**
     * Add cookie to response.
     * @param string $name Cookie name.
     * @param mixed $value Cookie value.
     * @param int $expire Expiration timestamp.
     * @return void
     */
    public function addCookie(string $name, $value, int $expire): void
    {
        try {
            $cookie = Cookie::create($name, (string)$value, (int)$expire);
            if ($this->response instanceof Response) {
                $this->response->headers->setCookie($cookie);
                return;
            }
            // best-effort: set on global response stack if exists
            if (function_exists('response')) {
                try {
                    $resp = response();
                    if ($resp instanceof Response) {
                        $resp->headers->setCookie($cookie);
                    }
                } catch (\Throwable) {}
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
