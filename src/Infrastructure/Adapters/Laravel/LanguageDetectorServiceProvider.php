<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Laravel ServiceProvider that boots LanguageDetector and sets app locale.
 * Register in config/app.php providers or via package auto-discovery.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Illuminate\Support\ServiceProvider;
use LanguageDetector\Application\LanguageDetector;

class LanguageDetectorServiceProvider extends ServiceProvider
{
    public string $paramName = 'lang';
    public string $default = 'en';
    public int $pathSegmentIndex = 0;

    public function register(): void
    {
        // Bind detector into the container
        $this->app->singleton('languageDetector', function ($app) {
            $config = [
                'paramName' => $this->paramName,
                'default' => $this->default,
                'pathSegmentIndex' => $this->pathSegmentIndex,
            ];

            $context = new LaravelContext($config);

            return new LanguageDetector($context, null, $config);
        });
    }

    public function boot(): void
    {
        try {
            /** @var LanguageDetector $detector */
            $detector = $this->app->make('languageDetector');
            $lang = $detector->detect(false);
            // apply to app locale
            $this->app->setLocale($lang);
            // expose on container
            $this->app->instance('languageDetector', $detector);
        } catch (\Throwable) {
            // fail silently to not break app bootstrap
        }
    }
}
