<?php

require __DIR__ . '/../vendor/autoload.php';

use LanguageDetector\Core\LanguageDetector;
use LanguageDetector\Adapters\Laravel\LaravelRequestAdapter;
use LanguageDetector\Adapters\Laravel\LaravelResponseAdapter;
use LanguageDetector\Adapters\Laravel\LaravelUserAdapter;
use LanguageDetector\Adapters\Laravel\LaravelLanguageRepository;
use Illuminate\Support\Str;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;

// --- Mocks ---
class DummyRequest {
    public $get = [];
    public $post = [];
    public $cookies = [];
    public $session = [];
    public $headers = [];
    public $path = '/en/test';

    public function isConsole() { return false; }
    public function get($name) { return $this->get[$name] ?? null; }
    public function post($name) { return $this->post[$name] ?? null; }
    public function hasHeader($name) { return isset($this->headers[$name]); }
    public function getHeader($name) { return $this->headers[$name] ?? null; }
    public function hasCookie($name) { return isset($this->cookies[$name]); }
    public function getCookie($name) { return $this->cookies[$name] ?? null; }
    public function hasSession() { return true; }
    public function getSession($name) { return $this->session[$name] ?? null; }
    public function setSession($name, $value) { $this->session[$name] = $value; }
    public function getPath() { return $this->path; }
}

class DummyResponse {
    public $cookies = [];
    public function addCookie($name, $value, $expire) { $this->cookies[$name] = $value; }
}

class DummyUser {
    private $attributes = [];
    public function isGuest() { return false; }
    public function getAttribute($name) { return $this->attributes[$name] ?? null; }
    public function setAttribute($name, $value) { $this->attributes[$name] = $value; }
    public function saveAttributes(array $names) { /* noop */ }
}

// --- <?php

require __DIR__ . '/../vendor/autoload.php';

use LanguageDetector\Core\LanguageDetector;
use LanguageDetector\Adapters\Laravel\LaravelRequestAdapter;
use LanguageDetector\Adapters\Laravel\LaravelResponseAdapter;
use LanguageDetector\Adapters\Laravel\LaravelUserAdapter;
use LanguageDetector\Adapters\Laravel\LaravelLanguageRepository;
use Illuminate\Support\Str;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;

// --- Mocks ---
class DummyRequest {
    public $get = [];
    public $post = [];
    public $cookies = [];
    public $session = [];
    public $headers = [];
    public $path = '/en/test';

    public function isConsole() { return false; }
    public function get($name) { return $this->get[$name] ?? null; }
    public function post($name) { return $this->post[$name] ?? null; }
    public function hasHeader($name) { return isset($this->headers[$name]); }
    public function getHeader($name) { return $this->headers[$name] ?? null; }
    public function hasCookie($name) { return isset($this->cookies[$name]); }
    public function getCookie($name) { return $this->cookies[$name] ?? null; }
    public function hasSession() { return true; }
    public function getSession($name) { return $this->session[$name] ?? null; }
    public function setSession($name, $value) { $this->session[$name] = $value; }
    public function getPath() { return $this->path; }
}

class DummyResponse {
    public $cookies = [];
    public function addCookie($name, $value, $expire) { $this->cookies[$name] = $value; }
}

class DummyUser {
    private $attributes = [];
    public function isGuest() { return false; }
    public function getAttribute($name) { return $this->attributes[$name] ?? null; }
    public function setAttribute($name, $value) { $this->attributes[$name] = $value; }
    public function saveAttributes(array $names) { /* noop */ }
}

// --- Initializing adapters ---
$requestAdapter = new LaravelRequestAdapter($req = new DummyRequest());
$responseAdapter = new LaravelResponseAdapter($resp = new DummyResponse());
$userAdapter = new LaravelUserAdapter($user = new DummyUser());

// --- A simple repository model for testing ---
$languageRepo = new class implements LanguageDetector\Core\Contracts\LanguageRepositoryInterface {
    public function getEnabledLanguageCodes(): array { return ['en','uk','fr']; }
};

// PSR-16 cache
$cache = new CacheRepository(new ArrayStore());

// --- Detector ---
$detector = new LanguageDetector(
    $requestAdapter,
    $responseAdapter,
    $userAdapter,
    $languageRepo,
    $cache,
    ['default'=>'en']
);

// --- Tests ---
echo "Test path (/en/test): " . $detector->detect() . "\n";

// GET parameter
$req->get['lang'] = 'uk';
echo "Test GET parameter: " . $detector->detect() . "\n";

// POST parameter
$req->get = []; // clear previous GET
$req->post['lang'] = 'fr';
echo "Test POST parameter: " . $detector->detect() . "\n";

// Cookie
$req->post = [];
$req->cookies['lang'] = 'uk';
echo "Test Cookie: " . $detector->detect() . "\n";

// Session
$req->cookies = [];
$req->session['lang'] = 'fr';
echo "Test Session: " . $detector->detect() . "\n";

// --- User attribute ---
$user->setAttribute('language_code','uk');
$req->session = [];
echo "Test User profile: " . $detector->detect() . "\n";

// --- Header Accept-Language ---
$user->setAttribute('language_code', null);
$req->headers['Accept-Language'] = 'fr-FR,fr;q=0.9,en;q=0.8';
echo "Test Accept-Language header: " . $detector->detect() . "\n";
// ---
$requestAdapter = new LaravelRequestAdapter($req = new DummyRequest());
$responseAdapter = new LaravelResponseAdapter($resp = new DummyResponse());
$userAdapter = new LaravelUserAdapter($user = new DummyUser());

// --- A simple repository model for testing ---
$languageRepo = new class implements LanguageDetector\Core\Contracts\LanguageRepositoryInterface {
    public function getEnabledLanguageCodes(): array { return ['en','uk','fr']; }
};

// PSR-16 cache
$cache = new CacheRepository(new ArrayStore());

// --- Detector ---
$detector = new LanguageDetector(
    $requestAdapter,
    $responseAdapter,
    $userAdapter,
    $languageRepo,
    $cache,
    ['default'=>'en']
);

// --- Tests ---
echo "Test path (/en/test): " . $detector->detect() . "\n";

// GET parameter
$req->get['lang'] = 'uk';
echo "Test GET parameter: " . $detector->detect() . "\n";

// POST parameter
$req->get = []; // clear previous GET
$req->post['lang'] = 'fr';
echo "Test POST parameter: " . $detector->detect() . "\n";

// Cookie
$req->post = [];
$req->cookies['lang'] = 'uk';
echo "Test Cookie: " . $detector->detect() . "\n";

// Session
$req->cookies = [];
$req->session['lang'] = 'fr';
echo "Test Session: " . $detector->detect() . "\n";

// User attribute
$user->setAttribute('language_code','uk');
$req->session = [];
echo "Test User profile: " . $detector->detect() . "\n";

// Header Accept-Language
$user->setAttribute('language_code', null);
$req->headers['Accept-Language'] = 'fr-FR,fr;q=0.9,en;q=0.8';
echo "Test Accept-Language header: " . $detector->detect() . "\n";
