<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;

use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

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
