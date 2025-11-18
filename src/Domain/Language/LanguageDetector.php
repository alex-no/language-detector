<?php
declare(strict_types=1);

namespace Domain\Language;

use LanguageDetector\Domain\Language\Contracts\SourceInterface;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\ResponseInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;
use LanguageDetector\Domain\Language\Contracts\LanguageRepositoryInterface;
use LanguageDetector\Domain\Language\Events\LanguageChangedEvent;
use Psr\SimpleCache\CacheInterface;

class LanguageDetector
{
   private const COOKIE_LIFETIME = 3600 * 24 * 365;

    private RequestInterface $request;
    private ResponseInterface $response;
    private ?UserInterface $user;
    private LanguageRepositoryInterface $repo;
    private CacheInterface $cache;
    private ?EventDispatcherInterface $dispatcher;

    /** @var SourceInterface[] */
    private array $sources = [];

    private string $paramName;
    private string $default;
    private string $cacheKey;
    private int $cacheTtl;
    private int $pathSegmentIndex;

    private const DEFAULT_CONFIG = [
        'paramName'        => 'lang',
        'default'          => 'en',
        'cacheKey'         => 'allowed_languages',
        'cacheTtl'         => 3600,
        'pathSegmentIndex' => 0,
    ];

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param UserInterface|null $user
     * @param LanguageRepositoryInterface $repo
     * @param CacheInterface $cache
     * @param EventDispatcherInterface|null $dispatcher
     * @param array $config  Optional config:
     *                      - paramName, default, cacheKey, cacheTtl, pathSegmentIndex
     *                      - sources: array of SourceInterface instances (optional)
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ?UserInterface $user,
        LanguageRepositoryInterface $repo,
        CacheInterface $cache,
        ?EventDispatcherInterface $dispatcher = null,
        array $config = []
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->user = $user;
        $this->repo = $repo;
        $this->cache = $cache;
        $this->dispatcher = $dispatcher;

        foreach (self::DEFAULT_CONFIG as $k => $v) {
            $this->$k = $config[$k] ?? $v;
        }

        if (!empty($config['sources']) && is_array($config['sources'])) {
            foreach ($config['sources'] as $s) {
                if ($s instanceof SourceInterface) {
                    $this->sources[] = $s;
                }
            }
        } else {
            // build default sources order: POST -> GET -> PATH -> user -> session -> cookie -> header -> default
            $this->sources = $this->buildDefaultSources();
        }
    }

    /**
     * Build default Source instances using fully-qualified classes in Infrastructure.
     *
     * Implemented here for convenience so constructor remains simple.
     *
     * @return SourceInterface[]
     */
    protected function buildDefaultSources(): array
    {
        return [
            new \LanguageDetector\Domain\Language\Sources\PostSource($this->paramName),
            new \LanguageDetector\Domain\Language\Sources\GetSource($this->paramName),
            new \LanguageDetector\Domain\Language\Sources\PathSource($this->pathSegmentIndex),
            new \LanguageDetector\Domain\Language\Sources\UserProfileSource($this->paramName),
            new \LanguageDetector\Domain\Language\Sources\SessionSource($this->paramName),
            new \LanguageDetector\Domain\Language\Sources\CookieSource($this->paramName),
            new \LanguageDetector\Domain\Language\Sources\HeaderSource('Accept-Language'),
            new \LanguageDetector\Domain\Language\Sources\DefaultSource($this->default),
        ];
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

        return $this->default;
    }

    /**
     * Finalize: set session/cookie and update user profile + dispatch event.
     *
     * @param string $lang
     * @param bool $isApi
     * @return string
     */
    protected function finalize(string $lang, bool $isApi): string
    {
        if (!$isApi) {
            try {
                if ($this->request->hasSession()) {
                    $this->request->setSession($this->paramName, $lang);
                }
                $this->response->addCookie($this->paramName, $lang, time() + self::COOKIE_LIFETIME);
            } catch (\Throwable) {
                // ignore
            }
        }

        if ($this->user?->isGuest() === false) {
            try {
                $old = (string)$this->user->getAttribute($this->paramName);
                if ($old !== $lang) {
                    $this->user->setAttribute($this->paramName, $lang);
                    $this->user->saveAttributes([$this->paramName]);
                    if ($this->dispatcher) {
                        try {
                            $this->dispatcher->dispatch(new LanguageChangedEvent($old, $lang, $this->user));
                        } catch (\Throwable) {
                            // ignore
                        }
                    }
                }
            } catch (\Throwable) {
                // ignore
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

        arsort($prioritized, SORT_NUMERIC);

        $normalized = [];
        foreach ($prioritized as $lang => $q) {
            $short = strtolower(substr($lang, 0, 2));
            $normalized[$short] = max($normalized[$short] ?? 0.0, (float)$q);
        }
        arsort($normalized, SORT_NUMERIC);

        $valid = $this->getAllowedLanguages();

        foreach (array_keys($normalized) as $lang) {
            if (in_array($lang, $valid, true)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Parse Accept-Language header into [lang=>q].
     *
     * @param string $header
     * @return array
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
     * Get allowed languages from cache or repository.
     *
     * @return string[]
     */
    protected function getAllowedLanguages(): array
    {
        try {
            $cached = $this->cache->get($this->cacheKey);
            if (is_array($cached) && $cached !== []) {
                return $cached;
            }
        } catch (\Throwable) {
            // ignore
        }
        return $this->refreshAllowedLanguages();
    }

    /**
     * Refresh list from repository and update cache.
     *
     * @return string[]
     */
    protected function refreshAllowedLanguages(): array
    {
        try {
            $langs = $this->repo->getEnabledLanguageCodes();
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
