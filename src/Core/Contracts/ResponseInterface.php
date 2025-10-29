<?php
namespace LanguageDetector\Core\Contracts;
/**
 * Interface for framework response adapter
 * ResponseInterface.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Your Name <Oleksandr Nosov>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license    MIT
 * @link
 * @package    LanguageDetector\Core\Contracts
 * @author     Your Name <Oleksandr Nosov>
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
