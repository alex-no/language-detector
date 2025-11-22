<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * Yii2Context.php
 * Yii2 context adapter implementing FrameworkContextInterface.
 * Creates all adapters on the fly.
 *
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
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
use Yii;

final class Yii2Context implements FrameworkContextInterface
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
        return new YiiRequestAdapter(Yii::$app->request);
    }

    /**
     * @inheritDoc
     */
    public function getResponse(): ResponseInterface
    {
        return new YiiResponseAdapter(Yii::$app->response);
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?UserInterface
    {
        $identity = Yii::$app->has('user') ? Yii::$app->user->identity : null;
        return new YiiUserAdapter($identity);
    }

    /**
     * @inheritDoc
     */
    public function getCache(): CacheInterface
    {
        return new YiiCacheAdapter(Yii::$app->cache);
    }

    /**
     * @inheritDoc
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return new YiiEventDispatcher();
    }

    /**
     * @inheritDoc
     */
    public function getLanguageRepository(): LanguageRepositoryInterface
    {
        return new YiiLanguageRepository(Yii::$app->db);
    }
}
