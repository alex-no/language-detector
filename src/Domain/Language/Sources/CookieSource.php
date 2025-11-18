<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Language\Sources;
                       src\Domain\Language\Sources\CookieSource.php

use LanguageDetector\Domain\Language\Contracts\SourceInterface;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;

/**
 * CookieSource - reads language from cookie
 */
class CookieSource implements SourceInterface
{
    private string $param;

    public function __construct(string $param = 'lang')
    {
        $this->param = $param;
    }

    public function getKey(): string
    {
        return 'cookie';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        if ($isApi) {
            return null;
        }
        try {
            if (!$request->hasCookie($this->param)) {
                return null;
            }
            $val = $request->getCookie($this->param);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
