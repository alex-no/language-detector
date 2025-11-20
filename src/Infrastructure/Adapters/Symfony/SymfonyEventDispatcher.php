<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Wrapper for Symfony Event Dispatcher
 * to implement EventDispatcherInterface.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyDispatcher;

class SymfonyEventDispatcher implements EventDispatcherInterface
{
    /**
     * Constructor.
     * @param SymfonyDispatcher $dispatcher Symfony event dispatcher.
     */
    public function __construct(
        private SymfonyDispatcher $dispatcher
    ) {}

    /**
     * Dispatch an event.
     * @param object $event The event to dispatch.
     * @return object The dispatched event.
     */
    public function dispatch(object $event)
    {
        try {
            $this->dispatcher->dispatch($event);
        } catch (\Throwable) {
            // ignore
        }
        return $event;
    }
}
