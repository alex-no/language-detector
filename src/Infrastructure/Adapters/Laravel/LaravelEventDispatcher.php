<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Simple wrapper for Laravel events dispatcher
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;

class LaravelEventDispatcher implements EventDispatcherInterface
{
    /**
     * Constructor
     * @param LaravelDispatcher $dispatcher Laravel events dispatcher
     */
    public function __construct(
        private LaravelDispatcher $dispatcher
    ) {}

    /**
     * Dispatch an event.
     * @param object $event Event object
     * @return object The dispatched event
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
