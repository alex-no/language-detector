<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;

use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

/**
 * UserProfileSource - extracts language from authenticated user's attribute
 */
class UserProfileSource implements SourceInterface
{
    private string $attribute;

    public function __construct(string $attribute = 'language_code')
    {
        $this->attribute = $attribute;
    }

    public function getKey(): string
    {
        return 'user';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        try {
            if ($user === null || $user->isGuest()) {
                return null;
            }
            $val = $user->getAttribute($this->attribute);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
