<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;

use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

/**
 * HeaderSource - reads Accept-Language header (or other header)
 */
class HeaderSource implements SourceInterface
{
    private string $headerName;

    public function __construct(string $headerName = 'Accept-Language')
    {
        $this->headerName = $headerName;
    }

    public function getKey(): string
    {
        return 'header';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        try {
            if (!$request->hasHeader($this->headerName)) {
                return null;
            }
            return $request->getHeader($this->headerName);
        } catch (\Throwable) {
            return null;
        }
    }
}
