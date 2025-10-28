<?php
namespace LanguageDetector\Core\Contracts;

/**
 * Interface for repository that provides enabled languages.
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
