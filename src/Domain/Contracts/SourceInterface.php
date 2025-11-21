<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Contracts;
/**
 * SourceInterface.php
 * SourceInterface - contract for language sources
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Source returns language candidate(s) from a particular source (POST, GET, header, etc).
 * Implementations must return either a string, array, or null.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

interface SourceInterface
{
    /**
     * Attempt to extract language value(s) from source.
     *
     * @param RequestInterface $request
     * @param UserInterface|null $user
     * @param bool $isApi If true, source may avoid using session/cookies
     * @return string|array|null  Language code (string), prioritized array (['en','uk']), or null if nothing found.
     */
    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): string|array|null;

    /**
     * Unique key name for source (e.g. 'post', 'get', 'header').
     *
     * @return string
     */
    public function getKey(): string;
}
