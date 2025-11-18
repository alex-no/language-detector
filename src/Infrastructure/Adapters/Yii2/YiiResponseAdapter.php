<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * YiiResponseAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use yii\web\Response;
use yii\web\Cookie;
use LanguageDetector\Domain\Contracts\ResponseInterface;

class YiiResponseAdapter implements ResponseInterface
{
    /**
     * YiiResponseAdapter constructor.
     * @param Response $response
     */
    public function __construct(
        private Response $response,
    ) {}

    /**
     * @inheritDoc
     */
    public function addCookie(string $name, $value, int $expire): void
    {
        try {
            $cookie = new Cookie([
                'name' => $name,
                'value' => $value,
                'expire' => $expire,
            ]);
            $this->response->getCookies()->add($cookie);
        } catch (\Throwable) {
            // ignore
        }
    }
}
