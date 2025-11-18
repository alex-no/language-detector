<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Language\Contracts;

use LanguageDetector\Domain\Language\Contracts\RequestInterface;
use LanguageDetector\Domain\Language\Contracts\UserInterface;

/**
 * SourceInterface
 *
 * Source returns language candidate(s) from a particular source (POST, GET, header, etc).
 * Implementations must return either a string, array, or null.
 *
 * @package LanguageDetector\Domain\Language\Contracts
 */
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
