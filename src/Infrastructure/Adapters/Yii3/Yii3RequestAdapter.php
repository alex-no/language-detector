<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3RequestAdapter.php
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
use LanguageDetector\Domain\Contracts\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class Yii3RequestAdapter implements RequestInterface
{
    /**
     * Yii3RequestAdapter constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(
        private ServerRequestInterface $request,
    ) {}

    /**
     * @inheritDoc
     */
    public function isConsole(): bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): mixed
    {
        $params = $this->request->getQueryParams();
        return $params[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function post(string $name): mixed
    {
        $params = $this->request->getParsedBody();
        if (is_array($params)) {
            return $params[$name] ?? null;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): mixed
    {
        $values = $this->request->getHeader($name);
        return !empty($values) ? $values[0] : null;
    }

    /**
     * @inheritDoc
     */
    public function hasCookie(string $name): bool
    {
        $cookies = $this->request->getCookieParams();
        return isset($cookies[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getCookie(string $name): mixed
    {
        $cookies = $this->request->getCookieParams();
        return $cookies[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function hasSession(): bool
    {
        // In Yii3, session is accessed via SessionInterface dependency injection
        // This adapter assumes session data is available via request attributes
        $session = $this->request->getAttribute('session');
        return $session !== null;
    }

    /**
     * @inheritDoc
     */
    public function getSession(string $name): mixed
    {
        $session = $this->request->getAttribute('session');
        if ($session === null) {
            return null;
        }

        try {
            if (is_array($session)) {
                return $session[$name] ?? null;
            }
            if (is_object($session) && method_exists($session, 'get')) {
                return $session->get($name);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setSession(string $name, $value): void
    {
        $session = $this->request->getAttribute('session');
        if ($session === null) {
            return;
        }

        try {
            if (is_object($session) && method_exists($session, 'set')) {
                $session->set($name, $value);
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ?string
    {
        try {
            $uri = $this->request->getUri();
            $path = $uri->getPath();

            if ($path === null || $path === '') {
                return null;
            }

            // normalize: trim slashes
            $normalized = trim($path, "/ \t\n\r\0\x0B");
            return $normalized ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
