<?php
declare(strict_types=1);

namespace LanguageDetector\Domain\Sources;
/**
 * PostSource.php
 * This file is part of LanguageDetector package.
 * PostSource - extracts language from POST parameter
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

class PostSource implements SourceInterface
{
    /**
     * PostSource constructor.
     * @param string $param POST parameter name to read language from
     */
    public function __construct(
        private string $param = 'lang'
    ) {}

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return 'post';
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(RequestInterface $request, ?UserInterface $user, bool $isApi): ?string
    {
        try {
            $val = $request->post($this->param);
            return $val === '' ? null : (is_string($val) ? $val : (string)$val);
        } catch (\Throwable) {
            return null;
        }
    }
}
