<?php
namespace LanguageDetector\Domain\Contracts;
/**
 * Interface for framework response adapter
 * ResponseInterface.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
interface ResponseInterface
{
    /**
     * Add or update a cookie.
     * @param string $name
     * @param mixed $value
     * @param int $expire UNIX timestamp when the cookie expires
     */
    public function addCookie(string $name, $value, int $expire): void;
}
