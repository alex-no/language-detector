<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Request listener to detect language on kernel.request.
 * Register as service and tag with kernel.event_listener (event: kernel.request, priority high).
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Symfony\Component\HttpKernel\Event\RequestEvent;
use LanguageDetector\Application\LanguageDetector;

class RequestListener
{
    /**
     * Constructor.
     * @param LanguageDetector $detector Language detector service.
     */
    public function __construct(
        private LanguageDetector $detector
    ) {}

    /**
     * On kernel request event handler.
     * @param RequestEvent $event The request event.
     * @return void
     * @noinspection PhpUnused
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        try {
            $isMaster = $event->isMainRequest();
            if (!$isMaster) {
                return;
            }
            $lang = $this->detector->detect(false);
            $request = $event->getRequest();
            // set locale on request and Symfony kernel
            $request->setLocale($lang);
            $request->attributes->set('_locale', $lang);
            // also set global translator locale if available
            if (method_exists($request->getSession(), 'set')) {
                try {
                    $request->getSession()->set('lang', $lang);
                } catch (\Throwable) {}
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
