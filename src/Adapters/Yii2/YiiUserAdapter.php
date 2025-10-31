<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * YiiUserAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use LanguageDetector\Core\Contracts\UserInterface;

class YiiUserAdapter implements UserInterface
{
    private $identity; // ActiveRecord representing user

    /**
     * YiiUserAdapter constructor.
     * @param $identity
     */
    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    /**
     * @inheritDoc
     */
    public function isGuest(): bool
    {
        // identity object exists, so not guest
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name): mixed
    {
        return $this->identity->{$name} ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, $value): void
    {
        $this->identity->{$name} = $value;
    }

    /**
     * @inheritDoc
     */
    public function saveAttributes(array $names): void
    {
        $this->identity->save(false, $names);
    }
}
