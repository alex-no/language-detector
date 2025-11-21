<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * DefaultSource.php
 * DefaultSource - always returns configured default language
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Sources
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

class DefaultSource implements SourceInterface
{
    /**
     * DefaultSource constructor.
     * @param string $default Default language to return
     */
    public function __construct(
        private string $default = 'en'
    ) {}

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return 'default';
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        return $this->default;
    }
}
