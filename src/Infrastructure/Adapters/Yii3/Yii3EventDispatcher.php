<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3EventDispatcher.php
 * Adapter to dispatch events in Yii3 applications.
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
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

class Yii3EventDispatcher implements EventDispatcherInterface
{
    /**
     * @param PsrEventDispatcherInterface $dispatcher
     */
    public function __construct(
        private PsrEventDispatcherInterface $dispatcher
    ) {}

    /**
     * Dispatch the event using PSR-14 EventDispatcher.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event): object
    {
        try {
            return $this->dispatcher->dispatch($event);
        } catch (\Throwable) {
            // If dispatch fails, return the original event
            return $event;
        }
    }
}
