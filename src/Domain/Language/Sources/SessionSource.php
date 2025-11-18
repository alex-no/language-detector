<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Language\Sources;

use LanguageDetector\Domain\Language\Contracts\SourceInterface;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;

/**
 * SessionSource - reads language from session
 */
class SessionSource implements SourceInterface
{
    private string $param;

    public function __construct(string $param = 'lang')
    {
        $this->param = $param;
    }

    public function getKey(): string
    {
        return 'session';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        if ($isApi) {
            return null;
        }
        try {
            if (!$request->hasSession()) {
                return null;
            }
            $val = $request->getSession($this->param);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
