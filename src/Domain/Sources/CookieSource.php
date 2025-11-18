<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;

use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

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
