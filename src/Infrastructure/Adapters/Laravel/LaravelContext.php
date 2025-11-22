<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * LaravelContext.php
 * Laravel context adapter implementing FrameworkContextInterface.
 * Creates all adapters on the fly.
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

final class LaravelContext implements FrameworkContextInterface
{
    /**
     * @param array $config Configuration options:
     *                      - paramName: string Parameter name for sources (default: 'lang')
     *                      - default: string Default language code (default: 'en')
     *                      - pathSegmentIndex: int Index of path segment for PathSource (default: 0)
     */
    public function __construct(private array $config) {}

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return new LaravelRequestAdapter(request());
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ResponseInterface
    {
        return new LaravelResponseAdapter(response());
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?UserInterface
    {
        return new LaravelUserAdapter(Auth::user());
    }

    /**
     * @inheritDoc
     */
    public function getCache(): CacheInterface
    {
        return new LaravelCacheAdapter(Cache::store());
    }

    /**
     * @inheritDoc
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return new LaravelEventDispatcher(Event::getFacadeRoot());
    }

    /**
     * @inheritDoc
     */
    public function getLanguageRepository(): LanguageRepositoryInterface
    {
        return new LaravelLanguageRepository(DB::connection());
    }
}
