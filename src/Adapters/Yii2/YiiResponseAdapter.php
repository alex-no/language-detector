<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * YiiResponseAdapter.php
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
use LanguageDetector\Core\Contracts\ResponseInterface;
use yii\web\Response;
use yii\web\Cookie;

class YiiResponseAdapter implements ResponseInterface
{
    private Response $response;

    /**
     * YiiResponseAdapter constructor.
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function addCookie(string $name, $value, int $expire): void
    {
        $this->response->cookies->add(new Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
        ]));
    }
}
