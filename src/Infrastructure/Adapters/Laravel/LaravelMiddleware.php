<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelMiddleware.php
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Closure;
use LanguageDetector\Application\LanguageDetector;
use Illuminate\Http\Request;

class LaravelMiddleware
{
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $detector = app(LanguageDetector::class);
        $lang = $detector->detect($request->isJson());
        app()->setLocale($lang);
        return $next($request);
    }
}
