<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * LanguageMiddleware.php
 * Middleware for Yii3 language detection.
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
use LanguageDetector\Application\LanguageDetector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LanguageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LanguageDetector $detector,
    ) {}

    /**
     * Process an incoming server request and detect language.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // Detect language
            $lang = $this->detector->detect(false);

            // Store detected language as request attribute for use in application
            $request = $request->withAttribute('language', $lang);
        } catch (\Throwable) {
            // If detection fails, continue without setting language
        }

        return $handler->handle($request);
    }
}
