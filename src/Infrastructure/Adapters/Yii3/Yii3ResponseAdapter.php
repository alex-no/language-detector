<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3ResponseAdapter.php
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
use LanguageDetector\Domain\Contracts\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;

class Yii3ResponseAdapter implements ResponseInterface
{
    /**
     * Yii3ResponseAdapter constructor.
     * @param PsrResponseInterface $response
     * @param CookieCollection|null $cookies Mutable cookie collection from context
     */
    public function __construct(
        private PsrResponseInterface $response,
        private ?CookieCollection $cookies = null,
    ) {}

    /**
     * @inheritDoc
     */
    public function addCookie(string $name, $value, int $expire): void
    {
        try {
            if ($this->cookies !== null) {
                $cookie = (new Cookie($name, (string)$value))
                    ->withExpires(new \DateTimeImmutable('@' . $expire));
                $this->cookies->add($cookie);
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
