<?php
namespace LanguageDetector\Core;

/**
 * Core language detector class
 * Detects user language based on request, user profile, cookies, etc.
 * Uses a set of resolvers in priority order.
 * Caches allowed languages from repository.
 * Finalizes by setting session, cookie, and updating user profile.
 * Configurable via constructor parameters.
 * @package LanguageDetector\Core
 */
use LanguageDetector\Core\Contracts\RequestInterface;
use LanguageDetector\Core\Contracts\ResponseInterface;
use LanguageDetector\Core\Contracts\UserInterface;
use LanguageDetector\Core\Contracts\LanguageRepositoryInterface;
use Psr\SimpleCache\CacheInterface;

class LanguageDetector
{
    private RequestInterface $request;
    private ResponseInterface $response;
    private ?UserInterface $user;
    private LanguageRepositoryInterface $repo;
    private CacheInterface $cache;
    private string $paramName = 'lang';
    private string $default = 'en';
    private string $userAttribute = 'language_code';
    private int $cacheTtl = 3600; // seconds
    private string $cacheKey = 'allowed_languages';

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param UserInterface|null $user
     * @param LanguageRepositoryInterface $repo
     * @param CacheInterface $cache
     * @param array $config  // paramName, default, userAttribute, cacheTtl, cacheKey
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

        if (isset($config['paramName'])) {
            $this->paramName = (string)$config['paramName'];
        }
        if (isset($config['default'])) {
            $this->default = (string)$config['default'];
        }
        if (isset($config['userAttribute'])) {
            $this->userAttribute = (string)$config['userAttribute'];
        }
        if (isset($config['cacheTtl'])) {
            $this->cacheTtl = (int)$config['cacheTtl'];
        }
        if (isset($config['cacheKey'])) {
            $this->cacheKey = (string)$config['cacheKey'];
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
                $this->response->addCookie($this->paramName, $lang, time() + 3600*24*365);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($this->user && !$this->user->isGuest()) {
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
        $prioritized = [];

        if (empty($input)) {
            return null;
        } elseif (is_array($input)) {
            foreach ($input as $lang) {
                $prioritized[(string)$lang] = 1.0;
            }
        } elseif (is_string($input)) {
            // If header contains multiple items separated by comma
            $entries = array_map('trim', explode(',', $input));
            foreach ($entries as $entry) {
                $parts = explode(';', $entry);
                $lang = trim($parts[0]);
                if ($lang === '') {
                    continue;
                }
                $q = 1.0;
                if (isset($parts[1]) && preg_match('/q=([0-9.]+)/i', $parts[1], $m)) {
                    $q = (float)$m[1];
                }
                $prioritized[$lang] = $q;
            }
        } else {
            // scalar (int/float) cast to string
            $prioritized[(string)$input] = 1.0;
        }

        if (empty($prioritized)) {
            return null;
        }

        // sort by priority desc
        arsort($prioritized, SORT_NUMERIC);

        // normalize to two-letter codes and keep priority
        $normalized = [];
        foreach (array_keys($prioritized) as $langCode) {
            $short = strtolower(substr($langCode, 0, 2));
            if (!isset($normalized[$short])) {
                $normalized[$short] = $prioritized[$langCode];
            }
        }

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
            // repo должен вернуть массив кодов: ['en', 'uk', ...]
            $langs = $this->repo->getEnabledLanguageCodes();
            if (!is_array($langs)) {
                $langs = [];
            }
            try {
                $this->cache->set($this->cacheKey, $langs, $this->cacheTtl);
            } catch (\Throwable $e) {
                // ignore cache set errors
            }
            return $langs;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
