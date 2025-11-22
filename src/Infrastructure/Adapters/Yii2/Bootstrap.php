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
use LanguageDetector\Infrastructure\Adapters\Yii2\Yii2Context;
use yii\base\BootstrapInterface;
use Yii;

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
 *         // 'sourceKeys' => ['get', 'header', 'default'], // Optional: custom source order
 *     ],
 * ],
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @var string $paramName GET/POST/Cookie/Session/UserProfile parameter name to read language from
     * @var string $default Default language code
     * @var int $pathSegmentIndex Index of the path segment to read language from
     * @var array|null $sourceKeys Custom order of language detection sources, or null for default order
     */
    public string $paramName = 'lang';
    public string $default = 'en';
    public int $pathSegmentIndex = 0;
    public ?array $sourceKeys = null;

    /**
     * @inheritDoc
     */
    public function bootstrap($app): void
    {
        try {
            $config = [
                'paramName' => $this->paramName,
                'default' => $this->default,
                'pathSegmentIndex' => $this->pathSegmentIndex,
            ];

            $context = new Yii2Context($config);

            $detector = new LanguageDetector($context, $this->sourceKeys, $config);


            // Apply detected language
            $lang = $detector->detect(false);
            Yii::$app->language = $lang;

            // Optionally expose detector to application for manual usage
            $app->set('languageDetector', $detector);
        } catch (\Throwable) {
            // Do not break bootstrap on errors; fail silently
        }
    }
}
