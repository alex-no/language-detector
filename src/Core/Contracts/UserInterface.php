<?php
namespace LanguageDetector\Core\Contracts;

/**
 * Interface representing an authenticated user.
 */
interface UserInterface
{
    /**
     * Whether the user is guest (not authenticated).
     */
    public function isGuest(): bool;

    /**
     * Get attribute value from the user model.
     */
    public function getAttribute(string $name);

    /**
     * Set attribute value on the user model.
     */
    public function setAttribute(string $name, $value): void;

    /**
     * Save specific attributes to persistent storage.
     *
     * @param string[] $names
     */
    public function saveAttributes(array $names): void;
}
