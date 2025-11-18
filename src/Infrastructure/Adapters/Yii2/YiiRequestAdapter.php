<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * YiiRequestAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Yii;
use yii\web\Request;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;

class YiiRequestAdapter implements RequestInterface
{
    /**
     * YiiRequestAdapter constructor.
     * @param Request $request
     */
    public function __construct(
        private Request $request,
    ) {}

    /**
     * @inheritDoc
     */
    public function isConsole(): bool
    {
        try {
            return Yii::$app->getRequest()->getIsConsoleRequest();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): mixed
    {
        return $this->request->get($name, null);
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
        try {
            return $this->request->getHeaders()->has($name);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): mixed
    {
        try {
            $h = $this->request->getHeaders()->get($name);
            return $h;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function hasCookie(string $name): bool
    {
        try {
            return $this->request->getCookies()->has($name);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getCookie(string $name): mixed
    {
        try {
            $cookie = $this->request->getCookies()->get($name);
            if ($cookie === null) {
                return null;
            }
            return $cookie->value ?? (string)$cookie;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function hasSession(): bool
    {
        try {
            return Yii::$app->has('session') && Yii::$app->get('session')->isActive;
        } catch (\Throwable) {
            return false;
        };
    }

    /**
     * @inheritDoc
     */
    public function getSession(string $name): mixed
    {
        if ($this->hasSession()) {
            try {
                return Yii::$app->getSession()->get($name, null);
            } catch (\Throwable) {
                return null;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setSession(string $name, $value): void
    {
        if ($this->hasSession()) {
            try {
                Yii::$app->getSession()->set($name, $value);
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ?string
    {
        try {
            // Prefer pathInfo (without script name and query)
            $path = $this->request->getPathInfo();
            if (!is_string($path) || $path === '') {
                // getUrl returns relative URL including query string, e.g. "/en/api?x=1"
                $url = $this->request->getUrl();
                if (is_string($url) && $url !== '') {
                    // strip query string
                    $qPos = strpos($url, '?');
                    $path = $qPos === false ? $url : substr($url, 0, $qPos);
                } else {
                    // fallback to server REQUEST_URI
                    $uri = $_SERVER['REQUEST_URI'] ?? null;
                    if (is_string($uri)) {
                        $qPos = strpos($uri, '?');
                        $path = $qPos === false ? $uri : substr($uri, 0, $qPos);
                    } else {
                        $path = null;
                    }
                }
            }

            if ($path === null) {
                return null;
            }

            // normalize: ensure string, trim slashes (we'll return without leading slash)
            $normalized = trim($path, "/ \t\n\r\0\x0B");
            return $normalized ?: null;
        } catch (\Throwable $e) {
            // on any error return null (defensive)
            return null;
        }
    }
}
