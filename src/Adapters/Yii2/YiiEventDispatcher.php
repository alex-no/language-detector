<?php
namespace LanguageDetector\Adapters;

use LanguageDetector\Core\Contracts\EventDispatcherInterface;

/**
 * YiiEventDispatcher
 *
 * Adapter to dispatch events in Yii2 applications.
 */
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
