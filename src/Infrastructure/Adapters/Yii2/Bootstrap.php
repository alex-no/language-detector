<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * Bootstrap.php
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Application\LanguageDetector;
use LanguageDetector\Domain\Sources\PostSource;
use LanguageDetector\Domain\Sources\GetSource;
use LanguageDetector\Domain\Sources\PathSource;
use LanguageDetector\Domain\Sources\UserProfileSource;
use LanguageDetector\Domain\Sources\SessionSource;
use LanguageDetector\Domain\Sources\CookieSource;
use LanguageDetector\Domain\Sources\HeaderSource;
use LanguageDetector\Domain\Sources\DefaultSource;
use yii\base\BootstrapInterface;

/**
 * Yii2 Bootstrap component for LanguageDetector.
 *
 * Usage (config/web.php):
 *
 * 'bootstrap' => ['languageBootstrap'],
 * 'components' => [
 *     'languageBootstrap' => [
 *         'class' => \LanguageDetector\Infrastructure\Adapters\Yii2\Bootstrap::class,
 *         'paramName' => 'lang',
 *         'default' => 'en',
 *         'pathSegmentIndex' => 0,
 *     ],
 * ],
 */
class Bootstrap implements BootstrapInterface
{
    public string $paramName = 'lang';
    public string $default = 'en';
    public int $pathSegmentIndex = 0;

    public function bootstrap($app): void
    {
        try {
            $requestAdapter = new YiiRequestAdapter($app->getRequest());
            $responseAdapter = new YiiResponseAdapter($app->getResponse());
            $userAdapter = new YiiUserAdapter($app->getUser()->identity ?? null);
            $repo = new YiiLanguageRepository($app->getDb());
            $cache = new YiiCacheAdapter($app->cache);
            $dispatcher = new YiiEventDispatcher('language.changed');

            // Build sources in default order. If you want to change order, create a custom list and pass to detector.
            $sources = [
                new PostSource($this->paramName),
                new GetSource($this->paramName),
                new PathSource($this->pathSegmentIndex),
                new UserProfileSource($this->paramName),
                new SessionSource($this->paramName),
                new CookieSource($this->paramName),
                new HeaderSource('Accept-Language'),
                new DefaultSource($this->default),
            ];

            $detector = new LanguageDetector(
                $requestAdapter,
                $responseAdapter,
                $userAdapter,
                $repo,
                $cache,
                $dispatcher,
                [
                    'paramName' => $this->paramName,
                    'default' => $this->default,
                    'pathSegmentIndex' => $this->pathSegmentIndex,
                    'sources' => $sources,
                ]
            );

            // Apply detected language
            $lang = $detector->detect(false);
            \Yii::$app->language = $lang;

            // Optionally expose detector to application for manual usage
            $app->set('languageDetector', $detector);
        } catch (\Throwable) {
            // Do not break bootstrap on errors; fail silently
        }
    }
}
