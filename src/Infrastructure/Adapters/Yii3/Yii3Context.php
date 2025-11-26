<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3Context.php
 * Yii3 context adapter implementing FrameworkContextInterface.
 * Creates all adapters on the fly.
 *
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii3
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\FrameworkContextInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\ResponseInterface;
use LanguageDetector\Domain\Contracts\UserInterface;
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Cache\CacheInterface as YiiCacheInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class Yii3Context implements FrameworkContextInterface
{
    /**
     * @param array $config Configuration options:
     *                      - paramName: string Parameter name for sources (default: 'lang')
     *                      - default: string Default language code (default: 'en')
     *                      - pathSegmentIndex: int Index of path segment for PathSource (default: 0)
     */
    public function __construct(
        private array $config,
        private ServerRequestInterface $request,
        private PsrResponseInterface $response,
        private ?IdentityInterface $identity,
        private YiiCacheInterface $cache,
        private PsrEventDispatcherInterface $eventDispatcher,
        private ConnectionInterface $db,
    ) {}

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return new Yii3RequestAdapter($this->request);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ResponseInterface
    {
        return new Yii3ResponseAdapter($this->response);
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?UserInterface
    {
        return new Yii3UserAdapter($this->identity);
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
        return new Yii3EventDispatcher($this->eventDispatcher);
    }

    /**
     * @inheritDoc
     */
    public function getLanguageRepository(): LanguageRepositoryInterface
    {
        return new Yii3LanguageRepository($this->db);
    }
}
