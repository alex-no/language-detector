<?php
use \yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $requestAdapter = new YiiRequestAdapter($app->request);
        $responseAdapter = new YiiResponseAdapter($app->response);
        $userAdapter = $app->user->isGuest ? null : new YiiUserAdapter($app->user->identity);
        $repo = new YiiLanguageRepository($app->db, 'language', 'code', 'is_enabled', 'order');
        $cache = new YiiCacheAdapter($app->cache);

        $detector = new \LanguageDetector\Core\LanguageDetector(
            $requestAdapter, $responseAdapter, $userAdapter, $repo, $cache, [
                'paramName' => 'lang',
                'default' => 'en',
                'userAttribute' => 'language_code',
            ]
        );

        $lang = $detector->detect(false);
        Yii::$app->language = $lang;
    }
}