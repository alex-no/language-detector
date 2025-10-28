<?php
namespace LanguageDetector\Adapters\Yii2;

use LanguageDetector\Core\Contracts\RequestInterface;
use yii\web\Request;

class YiiRequestAdapter implements RequestInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function isConsole(): bool
    {
        return \Yii::$app instanceof \yii\console\Application;
    }

    public function get(string $name)
    {
        return $this->request->get($name);
    }

    public function post(string $name)
    {
        return $this->request->post($name);
    }

    public function hasHeader(string $name): bool
    {
        return $this->request->headers->has($name);
    }

    public function getHeader(string $name)
    {
        return $this->request->headers->get($name);
    }

    public function hasCookie(string $name): bool
    {
        return $this->request->cookies->has($name);
    }

    public function getCookie(string $name)
    {
        return $this->request->cookies->getValue($name);
    }

    public function hasSession(): bool
    {
        return \Yii::$app->has('session');
    }

    public function getSession(string $name)
    {
        return \Yii::$app->session->get($name);
    }

    public function setSession(string $name, $value): void
    {
        if ($this->hasSession()) {
            \Yii::$app->session->set($name, $value);
        }
    }
}
