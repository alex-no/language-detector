<?php
namespace LanguageDetector\Adapters\Laravel;

use LanguageDetector\Core\Contracts\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;

/**
 * LaravelEventDispatcher
 *
 * Adapter to use Laravel's event dispatcher as a PSR-14 EventDispatcher.
 */
class LaravelEventDispatcher implements EventDispatcherInterface
{
    private LaravelDispatcher $dispatcher;

    public function __construct(LaravelDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dispatch the event using Laravel's dispatcher.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event)
    {
        $this->dispatcher->dispatch($event);
        return $event;
    }
}
