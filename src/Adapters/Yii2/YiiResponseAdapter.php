<?php
namespace LanguageDetector\Adapters\Yii2;

use LanguageDetector\Core\Contracts\ResponseInterface;
use yii\web\Response;
use yii\web\Cookie;

class YiiResponseAdapter implements ResponseInterface
{
    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function addCookie(string $name, $value, int $expire): void
    {
        $this->response->cookies->add(new Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
        ]));
    }
}
