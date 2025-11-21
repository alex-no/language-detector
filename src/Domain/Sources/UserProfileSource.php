<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * UserProfileSource.php
 * This file is part of LanguageDetector package.
 * UserProfileSource - extracts language from authenticated user's attribute
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

class UserProfileSource implements SourceInterface
{
    /**
     * UserProfileSource constructor.
     * @param string $attribute User attribute name to read language from
     */
    public function __construct(
        private string $attribute = 'language_code'
    ) {}

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return 'user';
    }

    /**
     * @inheritDoc
     */
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
