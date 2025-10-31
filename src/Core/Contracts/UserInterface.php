<?php
namespace LanguageDetector\Core\Contracts;
/**
 * Interface representing an authenticated user.
 * UserInterface.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Core\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
interface UserInterface
{
    /**
     * Check if the user is a guest (not authenticated).
     * @return bool
     */
    public function isGuest(): bool;

    /**
     * Get attribute value from the user model.
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name): mixed;

    /**
     * Set attribute value in the user model.
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, $value): void;

    /**
     * Save specified attributes to persistent storage.
     * @param string[] $names
     * @return void
     */
    public function saveAttributes(array $names): void;
}
