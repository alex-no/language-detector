<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * YiiUserAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use LanguageDetector\Domain\Language\Contracts\UserInterface;

class YiiUserAdapter implements UserInterface
{
    public function __construct(
        private $identity, // ActiveRecord representing user
    ) {}

    /**
     * @inheritDoc
     */
    public function isGuest(): bool
    {
        // identity object exists, so not guest
        try {
            if ($this->identity === null) {
                return true;
            }
            return false;
        } catch (\Throwable) {
            return true;
        }
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
            // try getter or array/prop access
            return $this->identity->{$name} ?? (is_array($this->identity) ? ($this->identity[$name] ?? null) : null);
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
            $this->identity->{$name} = $value;
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
            // prefer save(false, $names) if ActiveRecord; otherwise call save()
            if (method_exists($this->identity, 'save')) {
                // If ActiveRecord supports save($runValidation, $attributeNames)
                $ref = new \ReflectionMethod($this->identity, 'save');
                if ($ref->getNumberOfParameters() >= 2) {
                    $this->identity->save(false, $names);
                    return;
                }
                $this->identity->save();
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
