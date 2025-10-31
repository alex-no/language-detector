<?php
namespace LanguageDetector\Core;

/**
 * Core language detector class
 * Detects user language based on request, user profile, cookies, etc.
 * Uses a set of resolvers in priority order.
 * Caches allowed languages from repository.
 * Finalizes by setting session, cookie, and updating user profile.
 * Configurable via constructor parameters.
 * LanguageDetector.php
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Core
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Core\Contracts\RequestInterface;
use LanguageDetector\Core\Contracts\ResponseInterface;
use LanguageDetector\Core\Contracts\UserInterface;
use LanguageDetector\Core\Contracts\LanguageRepositoryInterface;
use Psr\SimpleCache\CacheInterface;

class LanguageDetector
{
    private const COOKIE_LIFETIME = 3600 * 24 * 365; // 1 year
    private const CONFIG_PARAMS = [
        'paramName'        => 'lang',              // GET/POST param name
        'default'          => 'en',                // default language code
        'userAttribute'    => 'language_code',     // user profile attribute name
        'cacheKey'         => 'allowed_languages', // cache key for allowed languages
        'cacheTtl'         => 3600,                // seconds
        'pathSegmentIndex' => 0,                   // which path segment to consider for language code, default 0
    ];

    private RequestInterface $request;
    private ResponseInterface $response;
    private ?UserInterface $user;
    private LanguageRepositoryInterface $repo;
    private CacheInterface $cache;
    private string $paramName;
    private string $default;
    private string $userAttribute;
    private string $cacheKey;
    private int $cacheTtl;
    private int $pathSegmentIndex;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param UserInterface|null $user
     * @param LanguageRepositoryInterface $repo
     * @param CacheInterface $cache
     * @param array $config  // paramName, default, userAttribute, cacheTtl, cacheKey, pathSegmentIndex
     */
    public function __construct(RequestInterface $request, ResponseInterface $response,
                                ?UserInterface $user, LanguageRepositoryInterface $repo,
                                CacheInterface $cache, array $config = [])
    {
        $this->request = $request;
        $this->response = $response;
        $this->user = $user;
        $this->repo = $repo;
        $this->cache = $cache;

        foreach (self::CONFIG_PARAMS as $configKey => $default) {
            $this->$configKey = $config[$configKey] ?? $default;
        }
    }

    /**
     * Detects user language.
     * @param bool $isApi  // if true, do not use session/cookie
     * @return string  // detected language code
     * @throws \Throwable
     */
    public function detect(bool $isApi = false): string
    {
        if ($this->request->isConsole()) {
            return $this->default;
        }

        $param = $this->paramName;

        $resolvers = [
            fn() => $this->extractValidLang($this->request->post($param)), // POST
            fn() => $this->extractValidLang($this->request->get($param)), // GET
            fn() => $this->extractValidLang($this->extractLangFromRequestPath()), // PATH
            fn() => ($this->user && !$this->user->isGuest()) ? $this->extractValidLang($this->user->getAttribute($this->userAttribute)) : null, // user profile
            fn() => (!$isApi && $this->request->hasSession()) ? $this->extractValidLang($this->request->getSession($param)) : null, // session
            fn() => (!$isApi && $this->request->hasCookie($param)) ? $this->extractValidLang($this->request->getCookie($param)) : null, // cookie
            fn() => $this->request->hasHeader('Accept-Language') ? $this->extractValidLang($this->request->getHeader('Accept-Language')) : null, // header
        ];

        foreach ($resolvers as $resolve) {
            try {
                $lang = $resolve();
            } catch (\Throwable $e) {
                // ignore single-source errors
                $lang = null;
            }
            if ($lang) {
                return $this->finalize($lang, $isApi);
            }
        }

        return $this->default;
    }

    /**
     * Finalizes detected language by setting session, cookie, and updating user profile.
     * @param string $lang
     * @param bool $isApi
     * @return string
     * @throws \Throwable
     */
    protected function finalize(string $lang, bool $isApi): string
    {
        if (!$isApi) {
            // set session & cookie via adapters
            try {
                if ($this->request->hasSession()) {
                    $this->request->setSession($this->paramName, $lang);
                }
                $this->response->addCookie($this->paramName, $lang, time() + self::COOKIE_LIFETIME);
            } catch (\Throwable $e) {
                // Ignore session/cookie errors
            }
        }

        if ($this->user?->isGuest() === false) {
            try {
                if ($this->user->getAttribute($this->userAttribute) !== $lang) {
                    $this->user->setAttribute($this->userAttribute, $lang);
                    $this->user->saveAttributes([$this->userAttribute]);
                }
            } catch (\Throwable $e) {
                // ignore user save errors
            }
        }

        return $lang;
    }

    /**
     * Extract and validate one or more language values
     * Accepts arrays, strings (Accept-Language), single code, etc.
     *
     * @param mixed $input
     * @return string|null
     */
    protected function extractValidLang($input): ?string
    {
      if ($input === null || $input === '' || $input === []) {
            return null;
        }

        $prioritized = match (true) {
            is_array($input) => array_fill_keys(array_map(strval(...), $input), 1.0),
            is_string($input) => $this->parseAcceptLanguageHeader($input),
            default => [(string)$input => 1.0],
        };

        if ($prioritized === []) {
            return null;
        }

        // Sort by priority descending
        arsort($prioritized, SORT_NUMERIC);

        // Normalize to 2-letter codes, preserve highest priority
        $normalized = [];
        foreach ($prioritized as $lang => $q) {
            $short = strtolower(substr($lang, 0, 2));
            $normalized[$short] = max($normalized[$short] ?? 0.0, $q);
        }
        arsort($normalized, SORT_NUMERIC);

        // allowed languages from repo/cache
        $valid = $this->getAllowedLanguages();

        foreach (array_keys($normalized) as $lang) {
            if (in_array($lang, $valid, true)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Parses Accept-Language header into prioritized array.
     * @param string $header
     * @return array  // [lang => qvalue, ...]
     */
    private function parseAcceptLanguageHeader(string $header): array
    {
        $prioritized = [];

        foreach (array_map('trim', explode(',', $header)) as $entry) {
            $parts = explode(';', $entry, 2);
            $lang = trim($parts[0]);
            if ($lang === '') {
                continue;
            }

            $q = 1.0;
            if (isset($parts[1])) {
                preg_match('/q=([0-9.]+)/i', $parts[1], $m);
                $q = $m[1] ?? 1.0;
            }

            $prioritized[$lang] = (float)$q;
        }

        return $prioritized;
    }

    /**
     * Tries to extract language code from request path.
     * Returns string|null (e.g. 'en', 'uk') or null if not found / invalid.
     *
     * This method attempts several common request methods to retrieve the path:
     *  - getPathInfo()
     *  - getPath()
     *  - getRequestUri()
     *  - getUri()
     *
     * It is defensive: if none of these methods exist on the Request adapter,
     * it returns null.
     *
     * @return string|null
     */
    protected function extractLangFromRequestPath(): ?string
    {
        try {
            $path = $this->request->getPath();
            if (empty($path) || !is_string($path)) {
                return null;
            }

            // take required segment
            $segments = array_filter(explode('/', trim($path, "/ \t\n\r\0\x0B")));
            $segment = $segments[$this->pathSegmentIndex] ?? '';
            if ($segment === '') {
                return null;
            }

            // normalize to two-letter
            $lang = strtolower(substr($segment, 0, 2));
            return in_array($lang, $this->getAllowedLanguages(), true) ? $lang : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Gets allowed languages from cache or repository.
     * @return string[]
     * @throws \Throwable
     */
    protected function getAllowedLanguages(): array
    {
        try {
            $cached = $this->cache->get($this->cacheKey);
        } catch (\Throwable $e) {
            $cached = false;
        }

        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        return $this->refreshAllowedLanguages();
    }

    /**
     * Refreshes allowed languages from repository and updates cache.
     * @return string[]
     * @throws \Throwable
     */
    protected function refreshAllowedLanguages(): array
    {
        try {
            // repo should return an array of codes: ['en', 'uk', ...]
            $langs = $this->repo->getEnabledLanguageCodes();
            $langs = is_array($langs) ? $langs : [];
            try {
                $this->cache->set($this->cacheKey, $langs, $this->cacheTtl);
            } catch (\Throwable) {
                // ignore cache set errors
            }
            return $langs;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
