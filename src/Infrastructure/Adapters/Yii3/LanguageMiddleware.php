<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;

use LanguageDetector\Infrastructure\Adapters\Yii3\Yii3LanguageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * LanguageMiddleware.php
 * Middleware for Yii3 language detection with cookie persistence.
 *
 * This middleware detects user language from multiple sources and persists
 * the selection in cookies. It works without requiring DI dependencies
 * that are unavailable during middleware construction.
 *
 * Priority order:
 * 1. GET parameter 'lang' (if present, saves to cookie)
 * 2. Cookie value (if no GET parameter)
 * 3. Default locale from config
 *
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii3
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
class LanguageMiddleware implements MiddlewareInterface
{
    private const COOKIE_LIFETIME = 365 * 24 * 60 * 60; // 1 year

    private string $paramName;
    private string $defaultLocale;
    private string $cookieName;
    private Yii3LanguageRepository $languageRepository;

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly CacheInterface $cache,
        array $config = []
    ) {
        $this->paramName = $config['paramName'] ?? 'lang';
        $this->defaultLocale = $config['default'] ?? 'en';
        $this->cookieName = $config['cookieName'] ?? 'app_language';

        // Create language repository for validating locales against database
        $this->languageRepository = new Yii3LanguageRepository(
            $this->db,
            $config['table'] ?? 'language',
            $config['codeField'] ?? 'code',
            $config['enabledField'] ?? 'is_enabled',
            $config['orderField'] ?? 'order'
        );
    }

    /**
     * Process an incoming server request and detect language.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $queryParams = $request->getQueryParams();
            $cookies = $request->getCookieParams();

            // Check if language is specified in query parameter
            $langFromQuery = $queryParams[$this->paramName] ?? null;

            // Get language from cookie
            $langFromCookie = $cookies[$this->cookieName] ?? null;

            // Determine current language
            $currentLocale = $langFromQuery ?? $langFromCookie;
            $currentLocale = $this->validateLocale($currentLocale);

            // Store detected language as request attribute for use in application
            $request = $request->withAttribute('language', $currentLocale);

            // Process request
            $response = $handler->handle($request);

            // If language was specified in query, update cookie
            if ($langFromQuery !== null && $this->isLocaleEnabled($langFromQuery)) {
                // Set cookie for 1 year
                $cookie = (new Cookie($this->cookieName, $langFromQuery))
                    ->withExpires(new \DateTimeImmutable('@' . (time() + self::COOKIE_LIFETIME)))
                    ->withPath('/')
                    ->withSameSite(Cookie::SAME_SITE_LAX);

                // Add cookie to response
                return $response->withAddedHeader('Set-Cookie', (string) $cookie);
            }

            return $response;
        } catch (\Throwable) {
            // If detection fails, continue without setting language
            // Use default locale
            $request = $request->withAttribute('language', $this->defaultLocale);
            return $handler->handle($request);
        }
    }

    /**
     * Get all enabled language codes from database
     *
     * @return string[] Array of language codes (e.g., ['en', 'uk', 'ru'])
     */
    private function getEnabledLocales(): array
    {
        try {
            // Try to get from cache first
            $cacheKey = 'language_detector_enabled_locales';
            $cached = $this->cache->get($cacheKey);

            if (is_array($cached) && !empty($cached)) {
                return $cached;
            }

            // Get from database
            $locales = $this->languageRepository->getEnabledLanguageCodes();

            // Cache for 1 hour
            $this->cache->set($cacheKey, $locales, 3600);

            return $locales;
        } catch (\Throwable) {
            return [$this->defaultLocale];
        }
    }

    /**
     * Check if a given locale is enabled
     *
     * @param string $locale Language code to check
     * @return bool True if locale is enabled, false otherwise
     */
    private function isLocaleEnabled(string $locale): bool
    {
        return in_array($locale, $this->getEnabledLocales(), true);
    }

    /**
     * Validate and return locale, fallback to default if invalid
     *
     * @param string|null $locale Language code to validate
     * @return string Valid locale code
     */
    private function validateLocale(?string $locale): string
    {
        if ($locale === null || $locale === '') {
            return $this->defaultLocale;
        }

        return $this->isLocaleEnabled($locale) ? $locale : $this->defaultLocale;
    }
}
