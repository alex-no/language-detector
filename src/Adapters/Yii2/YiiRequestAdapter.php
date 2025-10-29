<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * YiiRequestAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Your Name <Oleksandr Nosov>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license    MIT
 * @link
 * @package    LanguageDetector\Adapters\Yii2
 * @author     Your Name <Oleksandr Nosov>
 */
use Yii;
use yii\web\Request;
use LanguageDetector\Core\Contracts\RequestInterface;

class YiiRequestAdapter implements RequestInterface
{
    private Request $request;

    /**
     * YiiRequestAdapter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function isConsole(): bool
    {
        return Yii::$app instanceof \yii\console\Application;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): mixed
    {
        return $this->request->get($name);
    }

    /**
     * @inheritDoc
     */
    public function post(string $name): mixed
    {
        return $this->request->post($name);
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return $this->request->headers->has($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): mixed
    {
        return $this->request->headers->get($name);
    }

    /**
     * @inheritDoc
     */
    public function hasCookie(string $name): bool
    {
        return $this->request->cookies->has($name);
    }

    /**
     * @inheritDoc
     */
    public function getCookie(string $name): mixed
    {
        return $this->request->cookies->getValue($name);
    }

    /**
     * @inheritDoc
     */
    public function hasSession(): bool
    {
        return Yii::$app->has('session');
    }

    /**
     * @inheritDoc
     */
    public function getSession(string $name): mixed
    {
        return Yii::$app->session->get($name);
    }

    /**
     * @inheritDoc
     */
    public function setSession(string $name, $value): void
    {
        if ($this->hasSession()) {
            Yii::$app->session->set($name, $value);
        }
    }
}
