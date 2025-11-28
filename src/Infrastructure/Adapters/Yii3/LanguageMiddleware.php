<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;

use LanguageDetector\Application\LanguageDetector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Cache\CacheInterface;

/**
 * LanguageMiddleware.php
 * Middleware for Yii3 language detection.
 *
 * This middleware uses lazy initialization for Context to work around
 * Yii3 DI limitations where Request and Response are not available
 * during middleware construction.
 *
 * IMPORTANT: For user language persistence to work, ensure that:
 * 1. Authentication middleware runs BEFORE this middleware
 * 2. Identity is stored in request attributes as 'identity' or 'user'
 *
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
class LanguageMiddleware implements MiddlewareInterface
{
    private Yii3PartialContext $partialContext;
    private array $config;

    public function __construct(
        \PDO $pdo,
        CacheInterface $cache,
        array $config = []
    ) {
        $this->config = $config;

        // Create partial context with only available dependencies
        $this->partialContext = new Yii3PartialContext(
            $config,
            $pdo,
            $cache
        );
    }

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
            // Set request in context (available now)
            $this->partialContext->setRequest($request);

            // Note: Response is not needed for language detection
            // ResponseAdapter works with CookieCollection only, Response is optional

            // Detect language BEFORE calling handler
            $detector = new LanguageDetector($this->partialContext, null, $this->config);
            $lang = $detector->detect(false);

            // Add detected language to request attributes
            $request = $request->withAttribute('language', $lang);

            // Now call handler with updated request
            $response = $handler->handle($request);

            // Apply cookies from context to response
            $cookies = $this->partialContext->getCookies();
            foreach ($cookies as $cookie) {
                $response = $response->withAddedHeader('Set-Cookie', (string)$cookie);
            }

            return $response;
        } catch (\Throwable) {
            // If detection fails, continue without setting language
            return $handler->handle($request);
        }
    }
}
