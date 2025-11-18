<?php
namespace LanguageDetector\Domain\Language\Events;

use LanguageDetector\Domain\Language\Contracts\UserInterface;

class LanguageChangedEvent
{
    public string $oldLanguage;
    public string $newLanguage;
    public ?UserInterface $user;

    public function __construct(string $old, string $new, ?UserInterface $user)
    {
        $this->oldLanguage = $old;
        $this->newLanguage = $new;
        $this->user = $user;
    }
}
