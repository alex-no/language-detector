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
use LanguageDetector\Domain\Sources\PostSource;
use LanguageDetector\Domain\Sources\GetSource;
use LanguageDetector\Domain\Sources\PathSource;
use LanguageDetector\Domain\Sources\UserProfileSource;
use LanguageDetector\Domain\Sources\SessionSource;
use LanguageDetector\Domain\Sources\CookieSource;
use LanguageDetector\Domain\Sources\HeaderSource;
use LanguageDetector\Domain\Sources\DefaultSource;

class LanguageDetectorServiceProvider extends ServiceProvider
{
    public string $paramName = 'lang';
    public string $default = 'en';
    public int $pathSegmentIndex = 0;

    public function register(): void
    {
        // Bind adapters and detector into the container
        $this->app->singleton('languageDetector', function ($app) {
            $requestAdapter = new LaravelRequestAdapter($app['request']);
            $responseAdapter = new LaravelResponseAdapter($app['Illuminate\Contracts\Routing\ResponseFactory'] ?? $app['response']);
            $userAdapter = new LaravelUserAdapter($app['auth']->user() ?? null);
            $repo = new LaravelLanguageRepository($app['db']);
            $cache = new LaravelCacheAdapter($app['cache']->store());
            $dispatcher = new LaravelEventDispatcher($app['events']);

            $sources = [
                new PostSource($this->paramName),
                new GetSource($this->paramName),
                new PathSource($this->pathSegmentIndex),
                new UserProfileSource($this->paramName),
                new SessionSource($this->paramName),
                new CookieSource($this->paramName),
                new HeaderSource('Accept-Language'),
                new DefaultSource($this->default),
            ];

            return new LanguageDetector(
                $requestAdapter,
                $responseAdapter,
                $userAdapter,
                $repo,
                $cache,
                $dispatcher,
                [
                    'paramName' => $this->paramName,
                    'default' => $this->default,
                    'pathSegmentIndex' => $this->pathSegmentIndex,
                    'sources' => $sources,
                ]
            );
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
