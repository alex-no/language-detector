<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Language\Sources;

use LanguageDetector\Domain\Language\Contracts\SourceInterface;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;

/**
 * GetSource - extracts language from GET parameter
 */
class GetSource implements SourceInterface
{
    private string $param;

    public function __construct(string $param = 'lang')
    {
        $this->param = $param;
    }

    public function getKey(): string
    {
        return 'get';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        try {
            $val = $request->get($this->param);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
