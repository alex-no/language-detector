<?php
namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * LanguageDetector
 * Adapter to dispatch events in Yii2 applications.
 * PHP version 7.4+
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;

class YiiEventDispatcher implements EventDispatcherInterface
{
    private string $eventName;

    /**
     * @param string $eventName name of Yii2 event (default: 'language.changed')
     */
    public function __construct(string $eventName = 'language.changed')
    {
        $this->eventName = $eventName;
    }

    /**
     * Dispatch the event by triggering Yii::$app->trigger().
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event)
    {
        if (class_exists('\Yii')) {
            try {
                \Yii::$app->trigger($this->eventName, $event);
            } catch (\Throwable) {
                // ignore trigger errors
            }
        }
        return $event;
    }
}
