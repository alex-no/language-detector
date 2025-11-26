<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3UserAdapter.php
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii3
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\UserInterface;
use Yiisoft\Auth\IdentityInterface;

class Yii3UserAdapter implements UserInterface
{
    public function __construct(
        private ?IdentityInterface $identity,
    ) {}

    /**
     * @inheritDoc
     */
    public function isGuest(): bool
    {
        return $this->identity === null;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name): mixed
    {
        try {
            if ($this->identity === null) {
                return null;
            }
            // Try property access or array access
            if (is_object($this->identity)) {
                if (property_exists($this->identity, $name)) {
                    return $this->identity->{$name};
                }
                // Try getter method
                $getter = 'get' . ucfirst($name);
                if (method_exists($this->identity, $getter)) {
                    return $this->identity->{$getter}();
                }
            }
            if (is_array($this->identity)) {
                return $this->identity[$name] ?? null;
            }
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, $value): void
    {
        try {
            if ($this->identity === null) {
                return;
            }
            if (is_object($this->identity)) {
                if (property_exists($this->identity, $name)) {
                    $this->identity->{$name} = $value;
                    return;
                }
                // Try setter method
                $setter = 'set' . ucfirst($name);
                if (method_exists($this->identity, $setter)) {
                    $this->identity->{$setter}($value);
                }
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * @inheritDoc
     */
    public function saveAttributes(array $names): void
    {
        try {
            if ($this->identity === null) {
                return;
            }
            // If identity has a save method, call it
            if (is_object($this->identity) && method_exists($this->identity, 'save')) {
                $this->identity->save();
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Get user ID if available
     * @return string|null
     */
    public function getId(): ?string
    {
        if ($this->identity === null) {
            return null;
        }

        try {
            if (method_exists($this->identity, 'getId')) {
                return (string)$this->identity->getId();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
