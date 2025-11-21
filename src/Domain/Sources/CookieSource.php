<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * CookieSource.php
 * CookieSource - reads language from cookie
 * This file is part of LanguageDetector package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Sources
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

class CookieSource implements SourceInterface
{
    /**
     * CookieSource constructor.
     * @param string $param Cookie name to read language from
     */
    public function __construct(
        private string $param = 'lang'
    ) {}

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return 'cookie';
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        if ($isApi) {
            return null;
        }
        try {
            if (!$request->hasCookie($this->param)) {
                return null;
            }
            $val = $request->getCookie($this->param);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
