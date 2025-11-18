<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Language\Sources;

use LanguageDetector\Domain\Language\Contracts\SourceInterface;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;

/**
 * DefaultSource - always returns configured default language
 */
class DefaultSource implements SourceInterface
{
    private string $default;

    public function __construct(string $default = 'en')
    {
        $this->default = $default;
    }

    public function getKey(): string
    {
        return 'default';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        return $this->default;
    }
}
