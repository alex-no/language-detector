<?php
declare(strict_types=1);

namespace LanguageDetector\Application;
/**
 * Core language detector class
 * Detects user language based on request, user profile, cookies, etc.
 * Uses a set of resolvers in priority order.
 * Caches allowed languages from repository.
 * Finalizes by setting session, cookie, and updating user profile.
 * Configurable via constructor parameters.
 * LanguageDetector.php
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Application
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
// Import necessary interfaces (contracts)
use LanguageDetector\Domain\Contracts\FrameworkContextInterface;
use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;
use Psr\SimpleCache\CacheInterface;
// Import event class
use LanguageDetector\Domain\Events\LanguageChangedEvent;
// Import source classes (Sources)

class LanguageDetector
{
    private const COOKIE_LIFETIME = 3600 * 24 * 365;

    /** @var SourceInterface[] */
    private array $sources = [];

    private string $paramName;
    private string $default;
    private string $userAttribute;
    private string $cacheKey;
    private int $cacheTtl;
    private int $pathSegmentIndex;

    private RequestInterface $request;
    private UserInterface $user;
    private CacheInterface $cache;

    private const DEFAULT_CONFIG = [
        'paramName'        => 'lang',              // GET/POST/Cookie/Session param name
        'default'          => 'en',                // default language code
        'userAttribute'    => 'language_code',     // user profile attribute name (DB field)
        'cacheKey'         => 'allowed_languages', // cache key for allowed languages
        'cacheTtl'         => 3600,                // seconds
        'pathSegmentIndex' => 0,                   // which path segment to consider for language code, default 0
    ];
    private const DEFAULT_SOURCE_KEYS = [
        'post',
        'get',
        'path',
        'user',
        'session',
        'cookie',
        'header',
        'default',
    ];

    /**
     * @param FrameworkContextInterface $context Framework context providing request, response, user, cache, repo, dispatcher
     * @param array|null $sourceKeys Optional array of source keys to use in order (from DEFAULT_SOURCE_KEYS), or null for default order
     * @param array $config  Optional config:
     *                      - paramName, default, cacheKey, cacheTtl, pathSegmentIndex
     * @throws \Throwable
     */
    public function __construct(
        private readonly FrameworkContextInterface $context,
        ?array $sourceKeys = null,
        array $config = []
    ) {
        foreach (self::DEFAULT_CONFIG as $k => $v) {
            $this->$k = $config[$k] ?? $v;
        }

        $this->request = $context->getRequest();
        $this->user    = $context->getUser();
        $this->cache   = $context->getCache();

        $factory = new SourceFactory($config);

        $sourceKeys ??= self::DEFAULT_SOURCE_KEYS;

        $this->sources = $factory->make($sourceKeys);
    }

    /**
     * Detect language.
     *
     * @param bool $isApi If true, sources may skip session/cookies.
     * @return string
     */
    public function detect(bool $isApi = false): string
    {
        try {
            if ($this->request->isConsole()) {
                return $this->default;
            }
        } catch (\Throwable) {
            // defensive: if request fails, fallback to default
            return $this->default;
        }

        foreach ($this->sources as $source) {
            try {
                $raw = $source->getLanguage($this->request, $this->user, $isApi);
            } catch (\Throwable) {
                $raw = null;
            }
            if ($raw === null) {
                continue;
            }

            $lang = $this->extractValidLang($raw);
            if ($lang !== null) {
                return $this->finalize($lang, $isApi);
            }
        }

        // Always call finalize even for default language to save cookie/session
        return $this->finalize($this->default, $isApi);
    }

    /**
     * Finalizes detected language by setting session, cookie, and updating user profile.
     *
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
                $this->context->getResponse()->addCookie($this->paramName, $lang, time() + self::COOKIE_LIFETIME);
            } catch (\Throwable) {
                // Ignore session/cookie errors
            }
        }

        if ($this->user?->isGuest() === false) {
            // set user language via adapters
            try {
                $old = (string)$this->user->getAttribute($this->userAttribute);
                if ($old !== $lang) {
                    $this->user->setAttribute($this->userAttribute, $lang);
                    $this->user->saveAttributes([$this->userAttribute]);
                    $dispatcher = $this->context->getEventDispatcher();
                    if ($dispatcher) {
                        try {
                            $dispatcher->dispatch(new LanguageChangedEvent($old, $lang, $this->user));
                        } catch (\Throwable) {
                            // ignore
                        }
                    }
                }
            } catch (\Throwable) {
                // ignore user save errors
            }
        }

        return $lang;
    }

    /**
     * Accepts string|array and returns validated language code or null.
     *
     * @param mixed $input
     * @return string|null
     */
    protected function extractValidLang($input): ?string
    {
        if (empty($input)) {
            return null;
        }

        $prioritized = match (true) {
            is_array($input) => array_fill_keys(array_map('strval', $input), 1.0),
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
            $normalized[$short] = max($normalized[$short] ?? 0.0, (float)$q);
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
     *
     * @param string $header
     * @return array  // [lang => qvalue, ...]
     */
    private function parseAcceptLanguageHeader(string $header): array
    {
        $prioritized = [];
        foreach (array_map('trim', explode(',', $header)) as $entry) {
            if ($entry === '') {
                continue;
            }
            $parts = explode(';', $entry, 2);
            $lang = trim($parts[0]);
            $q = 1.0;
            if (isset($parts[1])) {
                if (preg_match('/q=([0-9.]+)/i', $parts[1], $m)) {
                    $q = (float)($m[1] ?? 1.0);
                }
            }
            $prioritized[$lang] = $q;
        }
        return $prioritized;
    }

    /**
     * Gets allowed languages from cache or repository.
     *
     * @return string[]
     * @throws \Throwable
     */
    protected function getAllowedLanguages(): array
    {
        try {
            $cached = $this->cache->get($this->cacheKey);
            if (is_array($cached) && $cached !== []) {
                return $cached;
            }
        } catch (\Throwable) {
            // ignore cache get errors
        }
        return $this->refreshAllowedLanguages();
    }

    /**
     * Refreshes allowed languages from repository and updates cache.
     *
     * @return string[]
     * @throws \Throwable
     */
    protected function refreshAllowedLanguages(): array
    {
        try {
            // repo should return an array of codes: ['en', 'uk', ...]
            $langs = $this->context->getLanguageRepository()->getEnabledLanguageCodes();
            $langs = is_array($langs) ? $langs : [];
            try {
                $this->cache->set($this->cacheKey, $langs, $this->cacheTtl);
            } catch (\Throwable) {
                // ignore cache set errors
            }
            return $langs;
        } catch (\Throwable) {
            return [];
        }
    }
}
