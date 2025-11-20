<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Adapter for Eloquent User (or any user object)
 * implements UserInterface.
 * Handles null user (guest) case.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\UserInterface;

class LaravelUserAdapter implements UserInterface
{
    /**
     * Constructor
     * @param mixed $user Laravel user object (Eloquent model) or null for guest
     */
    public function __construct(
        private $user // typically Eloquent model or null
    ) {}

    /**
     * Determine if user is guest (not authenticated).
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->user === null;
    }

    /**
     * Get a user attribute by name.
     * @param string $name Attribute name
     * @return mixed|null Attribute value or null if not present
     */
    public function getAttribute(string $name): mixed
    {
        try {
            if ($this->user === null) {
                return null;
            }
            return $this->user->{$name} ?? (is_array($this->user) ? ($this->user[$name] ?? null) : null);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Set a user attribute by name.
     * @param string $name Attribute name
     * @param mixed $value Attribute value
     * @return void
     */
    public function setAttribute(string $name, $value): void
    {
        try {
            if ($this->user === null) {
                return;
            }
            $this->user->{$name} = $value;
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Save user attributes (if applicable).
     * @param array $names Attribute names to save
     * @return void
     */
    public function saveAttributes(array $names): void
    {
        try {
            if ($this->user === null) {
                return;
            }
            if (method_exists($this->user, 'save')) {
                // If Eloquent supports save()
                $this->user->save();
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
