<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * PathSource.php
 * This file is part of LanguageDetector package.
 * PathSource - extracts language from URL path segment
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

class PathSource implements SourceInterface
{
    /**
     * PathSource constructor.
     * @param int $index Path segment index to read language from (0-based)
     */
    public function __construct(
        private int $index = 0
    ) {}

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return 'path';
    }

    /**
     * @inheritDoc
     */
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
