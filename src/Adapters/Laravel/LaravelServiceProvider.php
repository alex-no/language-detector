<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelServiceProvider.php
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Illuminate\Support\ServiceProvider;
use LanguageDetector\Core\LanguageDetector;
use LanguageDetector\Core\Contracts\RequestInterface;
use LanguageDetector\Core\Contracts\ResponseInterface;
use LanguageDetector\Core\Contracts\UserInterface;
use LanguageDetector\Core\Contracts\LanguageRepositoryInterface;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(RequestInterface::class, fn($app) => new LaravelRequestAdapter($app['request']));
        $this->app->bind(ResponseInterface::class, fn($app) => new LaravelResponseAdapter($app['response']));
        $this->app->bind(UserInterface::class, fn($app) => new LaravelUserAdapter($app['auth']->user()));
        $this->app->bind(LanguageRepositoryInterface::class, fn($app) => new LaravelLanguageRepository(config('language-detector.language_model')));

        $this->app->singleton(LanguageDetector::class, function($app) {
            return new LanguageDetector(
                $app->make(RequestInterface::class),
                $app->make(ResponseInterface::class),
                $app->make(UserInterface::class),
                $app->make(LanguageRepositoryInterface::class),
                $app['cache.store'],
                config('language-detector', [])
            );
        });
    }

    /**
     * Bootstrap services.
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/language-detector.php' => config_path('language-detector.php'),
        ], 'config');

        if (isset($this->app['router'])) {
            $router = $this->app['router'];
            $router->aliasMiddleware('language', LaravelMiddleware::class);
        }
    }
}
