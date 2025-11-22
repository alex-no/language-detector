<?php
declare(strict_types=1);
/**
 * TestLanguageDetector.php
 * Simple test script for LanguageDetector.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Tests
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */

require __DIR__ . '/../vendor/autoload.php';

use LanguageDetector\Application\LanguageDetector;
use LanguageDetector\Domain\Contracts\FrameworkContextInterface;
use LanguageDetector\Domain\Contracts\RequestInterface;
use LanguageDetector\Domain\Contracts\ResponseInterface;
use LanguageDetector\Domain\Contracts\UserInterface;
use LanguageDetector\Domain\Contracts\EventDispatcherInterface;
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;
use Psr\SimpleCache\CacheInterface;

// --- Mock Classes ---

/**
 * Mock Request implementation
 */
class MockRequest implements RequestInterface
{
    public array $get = [];
    public array $post = [];
    public array $cookies = [];
    public array $session = [];
    public array $headers = [];
    public string $path = '/en/test';

    public function isConsole(): bool { return false; }
    public function get(string $name): ?string { return $this->get[$name] ?? null; }
    public function post(string $name): ?string { return $this->post[$name] ?? null; }
    public function hasHeader(string $name): bool { return isset($this->headers[$name]); }
    public function getHeader(string $name): ?string { return $this->headers[$name] ?? null; }
    public function hasCookie(string $name): bool { return isset($this->cookies[$name]); }
    public function getCookie(string $name): ?string { return $this->cookies[$name] ?? null; }
    public function hasSession(): bool { return true; }
    public function getSession(string $name): ?string { return $this->session[$name] ?? null; }
    public function setSession(string $name, string $value): void { $this->session[$name] = $value; }
    public function getPath(): ?string { return $this->path; }
}

/**
 * Mock Response implementation
 */
class MockResponse implements ResponseInterface
{
    public array $cookies = [];

    public function addCookie(string $name, string $value, int $expire): void
    {
        $this->cookies[$name] = ['value' => $value, 'expire' => $expire];
    }
}

/**
 * Mock User implementation
 */
class MockUser implements UserInterface
{
    private array $attributes = [];
    private bool $guest = false;

    public function __construct(bool $guest = false)
    {
        $this->guest = $guest;
    }

    public function isGuest(): bool { return $this->guest; }
    public function getAttribute(string $name): mixed { return $this->attributes[$name] ?? null; }
    public function setAttribute(string $name, mixed $value): void { $this->attributes[$name] = $value; }
    public function saveAttributes(array $names): void { /* noop */ }
}

/**
 * Mock Cache implementation (PSR-16 SimpleCacheInterface)
 */
class MockCache implements CacheInterface
{
    private array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->data[$key] = $value;
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->data[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->data = [];
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
}

/**
 * Mock Language Repository
 */
class MockLanguageRepository implements LanguageRepositoryInterface
{
    public function getEnabledLanguageCodes(): array
    {
        return ['en', 'uk', 'fr', 'de'];
    }
}

/**
 * Mock Event Dispatcher
 */
class MockEventDispatcher implements EventDispatcherInterface
{
    public array $dispatchedEvents = [];

    public function dispatch(object $event): void
    {
        $this->dispatchedEvents[] = $event;
    }
}

/**
 * Mock Framework Context
 */
class MockContext implements FrameworkContextInterface
{
    public function __construct(
        private RequestInterface $request,
        private ResponseInterface $response,
        private ?UserInterface $user,
        private CacheInterface $cache,
        private LanguageRepositoryInterface $repository,
        private ?EventDispatcherInterface $dispatcher = null
    ) {}

    public function getRequest(): RequestInterface { return $this->request; }
    public function getResponse(): ResponseInterface { return $this->response; }
    public function getUser(): ?UserInterface { return $this->user; }
    public function getCache(): CacheInterface { return $this->cache; }
    public function getLanguageRepository(): LanguageRepositoryInterface { return $this->repository; }
    public function getEventDispatcher(): ?EventDispatcherInterface { return $this->dispatcher; }
}

// --- Test Runner ---

echo "=== Language Detector Tests ===\n\n";

// Initialize mocks
$request = new MockRequest();
$response = new MockResponse();
$user = new MockUser();
$cache = new MockCache();
$repository = new MockLanguageRepository();
$dispatcher = new MockEventDispatcher();

$context = new MockContext($request, $response, $user, $cache, $repository, $dispatcher);

$config = [
    'paramName' => 'lang',
    'default' => 'en',
    'pathSegmentIndex' => 0,
];

$detector = new LanguageDetector($context, null, $config);

// Test 1: Path detection
echo "Test 1 - Path (/en/test): ";
$result = $detector->detect();
echo ($result === 'en' ? "✓ PASS" : "✗ FAIL (expected: en, got: $result)") . "\n";

// Test 2: GET parameter
echo "Test 2 - GET parameter (lang=uk): ";
$request->get['lang'] = 'uk';
$result = $detector->detect();
echo ($result === 'uk' ? "✓ PASS" : "✗ FAIL (expected: uk, got: $result)") . "\n";

// Test 3: POST parameter
echo "Test 3 - POST parameter (lang=fr): ";
$request->get = [];
$request->post['lang'] = 'fr';
$result = $detector->detect();
echo ($result === 'fr' ? "✓ PASS" : "✗ FAIL (expected: fr, got: $result)") . "\n";

// Test 4: Cookie
echo "Test 4 - Cookie (lang=de): ";
$request->post = [];
$request->cookies['lang'] = 'de';
$result = $detector->detect();
echo ($result === 'de' ? "✓ PASS" : "✗ FAIL (expected: de, got: $result)") . "\n";

// Test 5: Session
echo "Test 5 - Session (lang=uk): ";
$request->cookies = [];
$request->session['lang'] = 'uk';
$result = $detector->detect();
echo ($result === 'uk' ? "✓ PASS" : "✗ FAIL (expected: uk, got: $result)") . "\n";

// Test 6: User attribute
echo "Test 6 - User profile (language_code=fr): ";
$request->session = [];
$user->setAttribute('lang', 'fr');
$result = $detector->detect();
echo ($result === 'fr' ? "✓ PASS" : "✗ FAIL (expected: fr, got: $result)") . "\n";

// Test 7: Accept-Language header
echo "Test 7 - Accept-Language header (de-DE,de;q=0.9,en;q=0.8): ";
$user->setAttribute('lang', null);
$request->headers['Accept-Language'] = 'de-DE,de;q=0.9,en;q=0.8';
$result = $detector->detect();
echo ($result === 'de' ? "✓ PASS" : "✗ FAIL (expected: de, got: $result)") . "\n";

// Test 8: Default fallback
echo "Test 8 - Default fallback: ";
$request->headers = [];
$request->path = '/unknown';
$result = $detector->detect();
echo ($result === 'en' ? "✓ PASS" : "✗ FAIL (expected: en, got: $result)") . "\n";

// Test 9: Invalid language code
echo "Test 9 - Invalid language (lang=invalid): ";
$request->get['lang'] = 'invalid';
$result = $detector->detect();
echo ($result === 'en' ? "✓ PASS" : "✗ FAIL (expected: en, got: $result)") . "\n";

// Test 10: Cache functionality
echo "Test 10 - Cache stores enabled languages: ";
$cachedLangs = $cache->get('allowed_languages');
$isArray = is_array($cachedLangs);
$hasEn = $isArray && in_array('en', $cachedLangs);
echo ($isArray && $hasEn ? "✓ PASS" : "✗ FAIL") . "\n";

echo "\n=== Tests Complete ===\n";
