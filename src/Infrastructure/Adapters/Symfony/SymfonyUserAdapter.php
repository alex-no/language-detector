<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Adapter for Symfony user (usually UserInterface or entity)
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\UserInterface;

class SymfonyUserAdapter implements UserInterface
{
    /**
     * Constructor.
     * @param mixed $identity user entity or null
     */
    public function __construct(
        private $identity // user entity or null
    ) {}

    /**
     * Check if user is guest.
     * @return bool True if guest, false if authenticated.
     */
    public function isGuest(): bool
    {
        return $this->identity === null;
    }

    /**
     * Get user attribute by name.
     * @param string $name Attribute name.
     * @return mixed Attribute value or null if not found.
     */
    public function getAttribute(string $name): mixed
    {
        try {
            if ($this->identity === null) {
                return null;
            }
            // try getter (getX / isX) then property
            $getter = 'get' . ucfirst($name);
            if (method_exists($this->identity, $getter)) {
                return $this->identity->{$getter}();
            }
            if (property_exists($this->identity, $name)) {
                return $this->identity->{$name};
            }
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Set user attribute by name.
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     * @return void
     */
    public function setAttribute(string $name, $value): void
    {
        try {
            if ($this->identity === null) {
                return;
            }
            $setter = 'set' . ucfirst($name);
            if (method_exists($this->identity, $setter)) {
                $this->identity->{$setter}($value);
                return;
            }
            if (property_exists($this->identity, $name)) {
                $this->identity->{$name} = $value;
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Save specified attributes to persistent storage.
     * @param array $names Attribute names to save.
     * @return void
     */
    public function saveAttributes(array $names): void
    {
        try {
            if ($this->identity === null) {
                return;
            }
            if (method_exists($this->identity, 'save')) {
                $this->identity->save();
                return;
            }
            // If Doctrine entity manager available externally, user of adapter should flush.
        } catch (\Throwable) {
            // ignore
        }
    }
}
