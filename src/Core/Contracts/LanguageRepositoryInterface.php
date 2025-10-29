<?php
namespace LanguageDetector\Core\Contracts;
/**
 * Interface for repository that provides enabled languages.
 * LanguageRepositoryInterface.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Your Name <Oleksandr Nosov>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license    MIT
 * @link
 * @package    LanguageDetector\Core\Contracts
 * @author     Your Name <Oleksandr Nosov>
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
