<?php
namespace LanguageDetector\Core\Contracts;

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
