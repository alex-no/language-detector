<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * HeaderSource.php
 * This file is part of LanguageDetector package.
 * HeaderSource - reads Accept-Language header (or other header)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

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
