<?php
namespace LanguageDetector\Domain\Contracts;
/**
 * FrameworkContextInterface.php
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Psr\SimpleCache\CacheInterface;

interface FrameworkContextInterface
{
    /**
     * Get the current request
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * Get the current response
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;

    /**
     * Get the current user, or null if not authenticated
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     * Get the cache instance
     * @return CacheInterface
     */
    public function getCache(): CacheInterface;

    /**
     * Get the event dispatcher, or null if not available
     * @return EventDispatcherInterface|null
     */
    public function getEventDispatcher(): ?EventDispatcherInterface;

    /**
     * Get the language repository
     * @return LanguageRepositoryInterface
     */
    public function getLanguageRepository(): LanguageRepositoryInterface;
}
