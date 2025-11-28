<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;

use LanguageDetector\Domain\Contracts\FrameworkContextInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\ResponseInterface;
use LanguageDetector\Domain\Contracts\UserInterface;
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Yiisoft\Cache\CacheInterface as YiiCacheInterface;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;

/**
 * Yii3PartialContext.php
 * Partial Yii3 context adapter that supports lazy initialization.
 *
 * This context is created with only DB and Cache in constructor,
 * and Request/Response are added later when they become available.
 *
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
final class Yii3PartialContext implements FrameworkContextInterface
{
    private ?ServerRequestInterface $request = null;
    private ?PsrResponseInterface $response = null;
    private ?CookieCollection $cookies = null;

    public function __construct(
        private array $config,
        private \PDO $pdo,
        private YiiCacheInterface $cache,
    ) {
        $this->cookies = new CookieCollection();
    }

    /**
     * Set request (called when request becomes available)
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Set response (called when response becomes available)
     */
    public function setResponse(PsrResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        if ($this->request === null) {
            throw new \RuntimeException('Request not available yet. Call setRequest() first.');
        }
        return new Yii3RequestAdapter($this->request);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ResponseInterface
    {
        if ($this->response === null) {
            throw new \RuntimeException('Response not available yet. Call setResponse() first.');
        }

        // Return response adapter with mutable cookie collection
        return new Yii3ResponseAdapter($this->response, $this->cookies);
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?UserInterface
    {
        // Guest user - we don't save language to user profile in Yii3
        return new Yii3UserAdapter(null);
    }

    /**
     * @inheritDoc
     */
    public function getCache(): CacheInterface
    {
        return new Yii3CacheAdapter($this->cache);
    }

    /**
     * @inheritDoc
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        // Dummy event dispatcher - we don't need events for now
        return new class implements EventDispatcherInterface {
            public function dispatch(object $event): void
            {
                // No-op
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function getLanguageRepository(): LanguageRepositoryInterface
    {
        return new Yii3LanguageRepository(
            $this->pdo,
            $this->config['table'] ?? 'language',
            $this->config['codeField'] ?? 'code',
            $this->config['enabledField'] ?? 'is_enabled',
            $this->config['orderField'] ?? 'order'
        );
    }

    /**
     * Get cookie collection (for applying cookies to response)
     */
    public function getCookies(): CookieCollection
    {
        return $this->cookies;
    }
}
