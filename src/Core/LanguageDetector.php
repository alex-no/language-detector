<?php
namespace LanguageDetector\Core;

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

    public function __construct(RequestInterface $request, ResponseInterface $response,
                                ?UserInterface $user, LanguageRepositoryInterface $repo,
                                CacheInterface $cache, array $config = [])
    {
        // заполнить проперти из $config
    }

    public function detect(bool $isApi = false): string
    {
        if ($this->request->isConsole()) {
            return $this->default;
        }

        $param = $this->paramName;

        $resolvers = [
            fn() => $this->extractValidLang($this->request->post($param)),
            fn() => $this->extractValidLang($this->request->get($param)),
            fn() => ($this->user && !$this->user->isGuest()) ? $this->extractValidLang($this->user->getAttribute($this->userAttribute)) : null,
            fn() => (!$isApi && $this->request->hasSession()) ? $this->extractValidLang($this->request->getSession($param)) : null,
            fn() => (!$isApi && $this->request->hasCookie($param)) ? $this->extractValidLang($this->request->getCookie($param)) : null,
            fn() => $this->request->hasHeader('Accept-Language') ? $this->extractValidLang($this->request->getHeader('Accept-Language')) : null,
        ];

        foreach ($resolvers as $resolve) {
            $lang = $resolve();
            if ($lang) {
                return $this->finalize($lang, $isApi);
            }
        }

        return $this->default;
    }

    protected function finalize(string $lang, bool $isApi): string
    {
        if (!$isApi) {
            $this->request->setSession($this->paramName, $lang);
            $this->response->addCookie($this->paramName, $lang, time() + 3600*24*365);
        }

        if ($this->user && !$this->user->isGuest()) {
            if ($this->user->getAttribute($this->userAttribute) !== $lang) {
                $this->user->setAttribute($this->userAttribute, $lang);
                $this->user->saveAttributes([$this->userAttribute]);
            }
        }

        return $lang;
    }

    protected function extractValidLang($input): ?string
    {
        // reuse your existing implementation of parsing & normalization
        // затем сравнить с $this->repo->getEnabledLanguageCodes()
    }

    protected function getAllowedLanguages(): array
    {
        $cacheKey = 'allowed_languages';
        return $this->cache->get($cacheKey) ?: $this->refreshAllowedLanguages($cacheKey);
    }
}
