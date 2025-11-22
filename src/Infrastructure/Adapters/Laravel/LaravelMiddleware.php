<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Laravel middleware to detect and apply language on each request.
 * Add to app/Http/Kernel.php in $middlewareGroups['web']
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LanguageDetector\Application\LanguageDetector;

class LaravelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            /** @var LanguageDetector $detector */
            $detector = app('languageDetector');

            if ($detector) {
                $lang = $detector->detect(false);
                App::setLocale($lang);
            }
        } catch (\Throwable) {
            // Fail silently to not break the request
        }

        return $next($request);
    }
}
