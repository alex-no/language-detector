<?php
namespace LanguageDetector\Domain\Contracts;
/**
 * EventDispatcherInterface.php
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

/**
 * EventDispatcherInterface
 *
 * Extends PSR-14 EventDispatcherInterface so package code can typehint
 * a local interface while staying compatible with PSR implementations.
 */
interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    // no new methods — this is an alias for clarity inside the package
}
