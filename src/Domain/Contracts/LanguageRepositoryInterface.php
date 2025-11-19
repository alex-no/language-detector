<?php
namespace LanguageDetector\Domain\Contracts;
/**
 * Interface for repository that provides enabled languages.
 * LanguageRepositoryInterface.php
 *
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
interface LanguageRepositoryInterface
{
    /**
     * Returns list of allowed language codes (e.g. ['en', 'uk', 'fr']).
     *
     * @return string[]
     */
    public function getEnabledLanguageCodes(): array;
}
