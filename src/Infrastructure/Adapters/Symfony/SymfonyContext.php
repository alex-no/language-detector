<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * SymfonyContext.php
 * Symfony context adapter implementing FrameworkContextInterface.
 * Creates all adapters on the fly.
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyDispatcher;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCache;
use Doctrine\DBAL\Connection;

final class SymfonyContext implements FrameworkContextInterface
{
    /**
     * @param RequestStack      $requestStack Symfony request stack.
     * @param SymfonyCache      $cache        Symfony cache instance.
     * @param SymfonyDispatcher $dispatcher   Symfony event dispatcher.
     * @param Connection        $connection   Doctrine DBAL connection.
     * @param array             $config       Configuration options:
     *                                        - paramName: string Parameter name for sources (default: 'lang')
     *                                        - default: string Default language code (default: 'en')
     *                                        - pathSegmentIndex: int Index of path segment for PathSource (default: 0)
     */
    public function __construct(
        private RequestStack $requestStack,
        private SymfonyCache $cache,
        private SymfonyDispatcher $dispatcher,
        private Connection $connection,
        private array $config
    ) {}

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return new SymfonyRequestAdapter($this->requestStack->getCurrentRequest());
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ResponseInterface
    {
        return new SymfonyResponseAdapter();
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?UserInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $request?->getUser();
        return $user ? new SymfonyUserAdapter($user) : null;
    }

    /**
     * @inheritDoc
     */
    public function getCache(): CacheInterface
    {
        return new SymfonyCacheAdapter($this->cache);
    }

    /**
     * @inheritDoc
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return new SymfonyEventDispatcher($this->dispatcher);
    }

    /**
     * @inheritDoc
     */
    public function getLanguageRepository(): LanguageRepositoryInterface
    {
        return new SymfonyLanguageRepository($this->connection);
    }
}
