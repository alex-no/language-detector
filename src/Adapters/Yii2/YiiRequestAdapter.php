<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * YiiRequestAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Yii;
use yii\web\Request;
use LanguageDetector\Core\Contracts\RequestInterface;

class YiiRequestAdapter implements RequestInterface
{
    private Request $request;

    /**
     * YiiRequestAdapter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function isConsole(): bool
    {
        return Yii::$app instanceof \yii\console\Application;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): mixed
    {
        return $this->request->get($name);
    }

    /**
     * @inheritDoc
     */
    public function post(string $name): mixed
    {
        return $this->request->post($name);
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
        return $this->request->headers->get($name);
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
        return $this->request->cookies->getValue($name);
    }

    /**
     * @inheritDoc
     */
    public function hasSession(): bool
    {
        return Yii::$app->has('session');
    }

    /**
     * @inheritDoc
     */
    public function getSession(string $name): mixed
    {
        return Yii::$app->session->get($name);
    }

    /**
     * @inheritDoc
     */
    public function setSession(string $name, $value): void
    {
        if ($this->hasSession()) {
            Yii::$app->session->set($name, $value);
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
