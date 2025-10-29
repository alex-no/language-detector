<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelResponseAdapter.php
 * This file is part of LanguageDetector package.
 * (c) Your Name <Oleksandr Nosov>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license    MIT
 * @package    LanguageDetector\Adapters\Laravel
 * @author     Your Name <Oleksandr Nosov>
 */
use LanguageDetector\Core\Contracts\ResponseInterface as CoreResponseInterface;
use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class LaravelResponseAdapter implements CoreResponseInterface
{
    /**
     * @param LaravelResponse $response
     */
    public function __construct(
        private LaravelResponse $response,
    ) {}

    /**
    * Add or update a cookie.
    * @param string $name
    * @param mixed $value
    * @param int $expire UNIX timestamp
    */
    public function addCookie(string $name, $value, int $expire): void
    {
        try {
            $lifetimeSeconds = max(0, (int)($expire - time()));
            // Symfony Cookie expects lifetime as expire timestamp or null; we'll pass expire timestamp
            $cookie = new SymfonyCookie($name, (string)$value, $expire);
            $this->response->headers->setCookie($cookie);


            // Also queue via Laravel cookie system so middleware that sends cookies will include it.
            if (function_exists('cookie')) {
                // Convert seconds to minutes for Cookie facade if needed
                $minutes = (int)ceil($lifetimeSeconds / 60.0);
                \Cookie::queue($name, (string)$value, $minutes);
            }
        } catch (\Throwable $e) {
            // ignore cookie errors
        }
    }
}
