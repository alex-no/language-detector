<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelUserAdapter.php
 * Adapter between Eloquent user (implements Authenticatable) and LanguageDetector\Domain\Contracts\UserInterface
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use LanguageDetector\Domain\Contracts\UserInterface as CoreUserInterface;
use Illuminate\Contracts\Auth\Authenticatable as LaravelAuthenticatable;

/**
 * LaravelUserAdapter.php
 * Adapter between Eloquent user (implements Authenticatable) and LanguageDetector\Domain\Contracts\UserInterface
 */
class LaravelUserAdapter implements CoreUserInterface
{
    /**
     * @param LaravelAuthenticatable|null $user
     */
    public function __construct(
        private ?LaravelAuthenticatable $user
    ) {}

    /**
     * @inheritDoc
     */
    public function isGuest(): bool
    {
        return $this->user === null;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name): mixed
    {
        if ($this->user === null) {
            return null;
        }

        // Most Eloquent models implement getAttribute()
        try {
            if (method_exists($this->user, 'getAttribute')) {
                return $this->user->getAttribute($name);
            }
            // fallback to property access
            return $this->user->{$name} ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, $value): void
    {
        if ($this->user === null) {
            return;
        }

        try {
            if (method_exists($this->user, 'setAttribute')) {
                $this->user->setAttribute($name, $value);
                return;
            }
            $this->user->{$name} = $value;
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Save specified attributes to persistent storage.
     * For Eloquent models we simply call save(); callers are expected to set attributes first.
     * @param string[] $names
     */
    public function saveAttributes(array $names): void
    {
        if ($this->user === null) {
            return;
        }

        try {
            // if model supports saveQuietly (Laravel 8+), prefer it to avoid events noise
            if (method_exists($this->user, 'saveQuietly')) {
                $this->user->saveQuietly();
                return;
            }
            $this->user->save();
        } catch (\Throwable $e) {
            // ignore save errors
        }
    }
}

