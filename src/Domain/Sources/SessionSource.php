<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * SessionSource.php
 * This file is part of LanguageDetector package.
 * SessionSource - reads language from session
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Domain\Contracts
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\UserInterface;

class SessionSource implements SourceInterface
{
    private string $param;

    public function __construct(string $param = 'lang')
    {
        $this->param = $param;
    }

    public function getKey(): string
    {
        return 'session';
    }

    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        if ($isApi) {
            return null;
        }
        try {
            if (!$request->hasSession()) {
                return null;
            }
            $val = $request->getSession($this->param);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
