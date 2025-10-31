<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * Bootstrap.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Yii;
use yii\base\BootstrapInterface;
use LanguageDetector\Core\LanguageDetector;

/**
 * Yii2 bootstrap adapter — creates the detector core and installs Yii::$app->language
 */
class Bootstrap implements BootstrapInterface
{
    public array $config = [];

    /**
     * Bootstrap constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param \yii\base\Application $app
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function bootstrap($app): void
    {
        // We create adapters that wrap Yii components.
        $requestAdapter = new YiiRequestAdapter($app->request);
        $responseAdapter = new YiiResponseAdapter($app->response);
        $userAdapter = $app->has('user') && !$app->user->isGuest && $app->user->identity
            ? new YiiUserAdapter($app->user->identity)
            : null;

        $repo = new YiiLanguageRepository(
            $app->db,
            $this->config['tableName'] ?? 'language',
            $this->config['codeField'] ?? 'code',
            $this->config['enabledField'] ?? 'is_enabled',
            $this->config['orderField'] ?? 'order'
        );

        $cache = new YiiCacheAdapter($app->cache);

        $detector = new LanguageDetector(
            $requestAdapter,
            $responseAdapter,
            $userAdapter,
            $repo,
            $cache,
            $this->config
        );

        // Detect and set application language
        try {
            $lang = $detector->detect(false);
            if ($lang) {
                $app->language = $lang;
            }
        } catch (\Throwable $e) {
            Yii::error('LanguageDetector error: ' . $e->getMessage());
           // Don't throw the exception any further—the app should still work.
        }

        // Save in container as a component
        $app->set('languageDetector', $detector);
    }
}
