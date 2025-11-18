<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;

use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

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
