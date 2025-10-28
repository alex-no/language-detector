<?php
namespace LanguageDetector\Adapters\Yii2;

use LanguageDetector\Core\Contracts\UserInterface;

class YiiUserAdapter implements UserInterface
{
    private $identity; // ActiveRecord representing user

    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    public function isGuest(): bool
    {
        // identity object exists, so not guest
        return false;
    }

    public function getAttribute(string $name)
    {
        return $this->identity->{$name} ?? null;
    }

    public function setAttribute(string $name, $value): void
    {
        $this->identity->{$name} = $value;
    }

    public function saveAttributes(array $names): void
    {
        $this->identity->save(false, $names);
    }
}
