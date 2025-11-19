<?php
namespace LanguageDetector\Domain\Events;
/**
 * LanguageChangedEvent.php
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Events
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\UserInterface;

class LanguageChangedEvent
{
    /**
     * LanguageChangedEvent constructor.
     * @param string $oldLanguage
     * @param string $newLanguage
     * @param UserInterface|null $user
     */
    public function __construct(
        public readonly string $oldLanguage,
        public readonly string $newLanguage,
        public readonly ?UserInterface $user = null,
    ) {}
}
