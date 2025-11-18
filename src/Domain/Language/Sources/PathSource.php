<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Language\Sources;

use LanguageDetector\Domain\Language\Contracts\SourceInterface;
use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;

/**
 * PathSource - extracts language from URL path segment
 */
class PathSource implements SourceInterface
{
    private int $index;

    public function __construct(int $index = 0)
    {
        $this->index = $index;
    }

    public function getKey(): string
    {
        return 'path';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        try {
            $path = $request->getPath();
            if ($path === null || $path === '') {
                return null;
            }
            $segments = array_values(array_filter(explode('/', trim($path, "/ \t\n\r\0\x0B"))));
            $seg = $segments[$this->index] ?? '';
            return $seg === '' ? null : (string)$seg;
        } catch (\Throwable) {
            return null;
        }
    }
}
