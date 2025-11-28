# ✨ Language Detector

[![Packagist Version](https://img.shields.io/packagist/v/alex-no/language-detector.svg)](https://packagist.org/packages/alex-no/language-detector)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/alex-no/language-detector)](https://www.php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-no/language-detector.svg)](https://packagist.org/packages/alex-no/language-detector)

A framework-agnostic language detection library for PHP 8.0+
with adapters for **Yii 2**, **Yii 3**, **Laravel**, and **Symfony**.

---

## ✨ Features
- **Multi-source language detection** with customizable priority (default order):
  1. POST parameter
  2. GET parameter
  3. URL Path segment
  4. Authenticated User Profile
  5. Session
  6. Cookie
  7. Accept-Language header
  8. Default language fallback
- **Customizable source order** — you can define which sources to use and in what order via `sourceKeys` configuration
- **Database-backed language list** — caches allowed languages from database with configurable TTL
- **Language persistence** — automatically saves detected language to session, cookie, and user profile (DB)
- **Separate configuration** — independent `paramName` (for GET/POST/Cookie/Session) and `userAttribute` (for DB field name)
- **API mode support** — works in both web and API contexts (API mode skips session/cookie)
- **Framework-agnostic** — clean DDD architecture with adapters for Yii 2, Yii 3, Laravel, and Symfony
- **Event system** — dispatches `LanguageChangedEvent` when user's language changes
- **Type-safe** — full PHP 8.0+ strict typing throughout

Starting from version 1.1.3, the package follows a clean DDD-inspired structure:

- **Domain** — interfaces (contracts), events, and pure business logic (Sources).
- **Application** — orchestrates domain services (e.g., LanguageDetector, SourceFactory).
- **Infrastructure** — framework adapters, repositories, cache, request/response bridges.

Each framework adapter implements `FrameworkContextInterface` which provides access to all framework-specific services (request, response, user, cache, repository, event dispatcher). This makes the library framework-agnostic and easy to extend.

---

## ⚙️ Installation

Install via Composer:

```bash
composer require alex-no/language-detector
```

---

## 🔔 Language change event

When the detector changes the stored language for a user (for example when a new `lang` parameter is provided or a higher-priority source selects another language), `LanguageDetector` will update the user's profile attribute and — if an event dispatcher is provided — dispatch a `LanguageDetector\Domain\Events\LanguageChangedEvent`.

The event object exposes three public properties:

- `oldLanguage` (string) — previous language code
- `newLanguage` (string) — new language code
- `user` (UserInterface|null) — the user instance (if available)

---

## 🚀 Usage in Yii 2

Register the bootstrap component in `config/web.php`:

```php
'bootstrap' => [
    'languageBootstrap',
],
'components' => [
    'languageBootstrap' => [
        'class' => \LanguageDetector\Infrastructure\Adapters\Yii2\Bootstrap::class,
        'paramName' => 'lang',              // GET/POST/Cookie/Session parameter name
        'userAttribute' => 'language_code', // User DB field name for storing language
        'default' => 'en',                  // Default language code
        'pathSegmentIndex' => 0,            // URL path segment index (0 = first segment)
    ],
],
```

The bootstrap component will:
- Automatically detect language on each request
- Check sources in priority order: POST → GET → Path → User → Session → Cookie → Header → Default
- Update `Yii::$app->language` accordingly
- Persist language to session, cookie, and user profile

**Manual usage:**

```php
// Access detector manually
$detector = Yii::$app->languageDetector;
$lang = $detector->detect();
```

**Custom source order:**

You can customize the detection order by passing `sourceKeys` in the configuration:

```php
'languageBootstrap' => [
    'class' => \LanguageDetector\Infrastructure\Adapters\Yii2\Bootstrap::class,
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
    'pathSegmentIndex' => 0,
    // Custom order: only check GET parameter and Accept-Language header
    'sourceKeys' => ['get', 'header', 'default'],
],
```

**Event handling:**

Listen to language change events using Yii's event system:

```php
Yii::$app->on('language.changed', function($event) {
    // $event is yii\base\Event
    // Access the LanguageChangedEvent object via $event->data
    $languageEvent = $event->data;
    echo "Language changed from {$languageEvent->oldLanguage} to {$languageEvent->newLanguage}";

    // Access user if available
    if ($languageEvent->user) {
        echo "User ID: " . $languageEvent->user->getId();
    }
});
```

**Note:** The language change event is currently dispatched **only for authenticated users**.

---

## 🚀 Usage in Yii 3

Yii3 adapter supports two usage approaches:

### Approach 1: Middleware (Recommended)

**1. Register the Middleware in DI**

Add to your DI configuration (typically in `config/web/di.php`):

```php
use LanguageDetector\Infrastructure\Adapters\Yii3\LanguageMiddleware;
use Yiisoft\Cache\CacheInterface;

return [
    LanguageMiddleware::class => static function (\PDO $pdo, CacheInterface $cache) {
        return new LanguageMiddleware(
            $pdo,
            $cache,
            [
                'paramName' => 'lang',              // GET/POST/Cookie/Session parameter name
                'userAttribute' => 'language_code', // User DB field name for storing language
                'default' => 'en',                  // Default language code
                'pathSegmentIndex' => 0,            // URL path segment index (0 = first segment)
                'table' => 'language',              // Database table name
                'codeField' => 'code',              // Language code field name
                'enabledField' => 'is_enabled',     // Enabled status field name
                'orderField' => 'order',            // Sort order field name
            ]
        );
    },
];
```

**2. Register Middleware in application stack**

Add to `config/web/application.php` (IMPORTANT: place AFTER authentication middleware):

```php
return [
    'middlewares' => [
        // ... other middlewares
        \Yiisoft\Auth\Middleware\Authentication::class, // Authentication MUST run first
        \LanguageDetector\Infrastructure\Adapters\Yii3\LanguageMiddleware::class,
        // ... other middlewares
    ],
];
```

**How it works:**
- Automatically detects language on each request
- Checks sources in priority order: POST → GET → Path → User → Session → Cookie → Header → Default
- Stores detected language as request attribute `language`
- Persists language to session, cookie, and authenticated user profile
- **Requires authentication middleware to run BEFORE** to enable user language persistence
- Identity must be stored in request attributes as `'identity'` or `'user'`

**Usage in controllers:**

```php
use Psr\Http\Message\ServerRequestInterface;

class HomeController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Get detected language from request attribute
        $lang = $request->getAttribute('language', 'en');

        // Use the detected language
        // ...

        return $this->render('home/index', ['lang' => $lang]);
    }
}
```

---

### Approach 2: Manual Usage with Full Context

For advanced scenarios where you need full control over all components:

**1. Register services in DI**

```php
use LanguageDetector\Infrastructure\Adapters\Yii3\Yii3Context;
use LanguageDetector\Application\LanguageDetector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

return [
    Yii3Context::class => static function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        ?IdentityInterface $identity,
        CacheInterface $cache,
        EventDispatcherInterface $eventDispatcher,
        ConnectionInterface $db
    ) {
        return new Yii3Context(
            [
                'paramName' => 'lang',
                'userAttribute' => 'language_code',
                'default' => 'en',
                'pathSegmentIndex' => 0,
            ],
            $request,
            $response,
            $identity,
            $cache,
            $eventDispatcher,
            $db
        );
    },

    LanguageDetector::class => static function (Yii3Context $context) {
        return new LanguageDetector($context, null, [
            'paramName' => 'lang',
            'userAttribute' => 'language_code',
            'default' => 'en',
            'pathSegmentIndex' => 0,
        ]);
    },
];
```

**2. Use in controllers:**

```php
use LanguageDetector\Application\LanguageDetector;

class HomeController
{
    public function index(LanguageDetector $detector): ResponseInterface
    {
        $lang = $detector->detect();
        return $this->render('home/index', ['lang' => $lang]);
    }
}
```

---

### Configuration Parameters

All configuration parameters for Yii3 adapter:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `paramName` | string | `'lang'` | Parameter name for GET/POST/Cookie/Session |
| `userAttribute` | string | `'language_code'` | User database field name for storing language |
| `default` | string | `'en'` | Default language code |
| `pathSegmentIndex` | int | `0` | URL path segment index for language detection (0 = first segment) |
| `table` | string | `'language'` | Database table name for enabled languages |
| `codeField` | string | `'code'` | Language code field name in database |
| `enabledField` | string | `'is_enabled'` | Enabled status field name (should contain 1/0) |
| `orderField` | string | `'order'` | Sort order field name |
| `cacheKey` | string | `'allowed_languages'` | Cache key for storing enabled languages |
| `cacheTtl` | int | `3600` | Cache TTL in seconds |

---

### Event Handling

Listen to `LanguageChangedEvent` using PSR-14 event listeners:

```php
use LanguageDetector\Domain\Events\LanguageChangedEvent;
use Psr\EventDispatcher\ListenerProviderInterface;

return [
    ListenerProviderInterface::class => static function () {
        $provider = new SimpleEventDispatcher();

        $provider->listen(LanguageChangedEvent::class, function (LanguageChangedEvent $event) {
            // Log or handle language change
            // Available properties: $event->oldLanguage, $event->newLanguage, $event->user
        });

        return $provider;
    },
];
```

**Note:** The language change event is dispatched **only for authenticated users**.

---

## 🚀 Usage in Laravel

**1. Register the Service Provider**

Add to the providers array in `config/app.php` (if not auto-discovered):

```php
'providers' => [
    LanguageDetector\Infrastructure\Adapters\Laravel\LanguageDetectorServiceProvider::class,
],
```

**2. Configure the Service Provider**

You can customize parameters directly in the service provider or extend it:

```php
// In config/app.php or create a custom service provider
'providers' => [
    \App\Providers\CustomLanguageServiceProvider::class,
],

// app/Providers/CustomLanguageServiceProvider.php
namespace App\Providers;

use LanguageDetector\Infrastructure\Adapters\Laravel\LanguageDetectorServiceProvider;

class CustomLanguageServiceProvider extends LanguageDetectorServiceProvider
{
    public string $paramName = 'lang';
    public string $userAttribute = 'language_code';  // User DB field name
    public string $default = 'en';
    public int $pathSegmentIndex = 0;  // URL path segment index
}
```

**3. Register the Middleware**

Add to `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \LanguageDetector\Infrastructure\Adapters\Laravel\LaravelMiddleware::class,
    ],
];
```

The middleware will:
- Automatically detect language on each request
- Apply it globally using `App::setLocale()`
- Check sources in priority order: POST → GET → Path → User → Session → Cookie → Header → Default

**Manual usage in controller:**

```php
use LanguageDetector\Application\LanguageDetector;
use Illuminate\Support\Facades\App;

public function index(LanguageDetector $detector)
{
    $lang = $detector->detect();
    App::setLocale($lang);

    return view('welcome', ['lang' => $lang]);
}
```

**Event handling:**

Listen to `LanguageChangedEvent` using Laravel event listeners:

```php
// In EventServiceProvider
use LanguageDetector\Domain\Events\LanguageChangedEvent;

protected $listen = [
    LanguageChangedEvent::class => [
        \App\Listeners\LogLanguageChange::class,
    ],
];
```

**Note:** The language change event is currently dispatched **only for authenticated users**.

---

## 🚀 Usage in Symfony

**1. Register the services**

Create or update `config/services.yaml`:

```yaml
services:
    # Register SymfonyContext
    LanguageDetector\Infrastructure\Adapters\Symfony\SymfonyContext:
        arguments:
            $requestStack: '@request_stack'
            $cache: '@cache.app'
            $dispatcher: '@event_dispatcher'
            $connection: '@doctrine.dbal.default_connection'
            $config:
                paramName: 'lang'
                userAttribute: 'language_code'
                default: 'en'
                pathSegmentIndex: 0

    # Register LanguageDetector
    LanguageDetector\Application\LanguageDetector:
        arguments:
            $context: '@LanguageDetector\Infrastructure\Adapters\Symfony\SymfonyContext'
            $sourceKeys: null  # Use default order, or customize: ['get', 'header', 'default']
            $config:
                paramName: 'lang'
                userAttribute: 'language_code'
                default: 'en'
                pathSegmentIndex: 0

    # Register RequestListener
    LanguageDetector\Infrastructure\Adapters\Symfony\RequestListener:
        arguments:
            $detector: '@LanguageDetector\Application\LanguageDetector'
        tags:
            - { name: kernel.event_listener, event: kernel.request, priority: 10 }
```

**2. How it works**

The `RequestListener` will:
- Listen to `kernel.request` event
- Automatically detect language on each request
- Set the locale on the request and session
- Update `$request->setLocale($lang)`

**Manual usage in controller:**

```php
use LanguageDetector\Application\LanguageDetector;

class HomeController extends AbstractController
{
    public function index(LanguageDetector $detector): Response
    {
        $lang = $detector->detect();
        $this->get('request_stack')->getCurrentRequest()->setLocale($lang);

        return $this->render('home/index.html.twig', [
            'language' => $lang,
        ]);
    }
}
```

**Event handling:**

Listen to `LanguageChangedEvent`:

```php
use LanguageDetector\Domain\Events\LanguageChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LanguageChangeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LanguageChangedEvent::class => 'onLanguageChanged',
        ];
    }

    public function onLanguageChanged(LanguageChangedEvent $event): void
    {
        // Log or handle language change
        // $event->oldLanguage, $event->newLanguage, $event->user
    }
}
```

**Note:** The language change event is currently dispatched **only for authenticated users**.

---

## ⚙️ Configuration Options

| Option             | Description                                          | Default | Used in              |
| ------------------ | ---------------------------------------------------- | ------- | -------------------- |
| `paramName`        | Request parameter name for language (GET/POST/etc)   | `lang`  | All adapters         |
| `default`          | Fallback language code                               | `en`    | All adapters         |
| `pathSegmentIndex` | URL path segment index for language detection        | `0`     | All adapters         |
| `sourceKeys`       | Array defining custom source detection order         | `null`  | All adapters         |
| `cacheKey`         | Cache key for storing allowed languages              | `allowed_languages` | LanguageDetector |
| `cacheTtl`         | Cache TTL in seconds                                 | `3600`  | LanguageDetector     |

**Note:** Repository-related options (`tableName`, `codeField`, `enabledField`, `orderField`) are configured within each framework's repository implementation, not in the main configuration.

---

## 🔍 Available Language Sources

You can customize which sources to use and their priority order via the `sourceKeys` configuration parameter. Available sources:

| Source Key  | Description                                                    | Class                |
| ----------- | -------------------------------------------------------------- | -------------------- |
| `post`      | Reads language from POST parameter (e.g., `$_POST['lang']`)    | `PostSource`         |
| `get`       | Reads language from GET parameter (e.g., `$_GET['lang']`)      | `GetSource`          |
| `path`      | Extracts language from URL path segment (e.g., `/en/home`)     | `PathSource`         |
| `user`      | Reads from authenticated user's profile attribute              | `UserProfileSource`  |
| `session`   | Reads from session storage                                     | `SessionSource`      |
| `cookie`    | Reads from cookie                                              | `CookieSource`       |
| `header`    | Parses Accept-Language HTTP header                             | `HeaderSource`       |
| `default`   | Returns the configured default language                        | `DefaultSource`      |

**Default order:** `['post', 'get', 'path', 'user', 'session', 'cookie', 'header', 'default']`

**Example custom order:**
```php
// Only use GET parameter and Accept-Language header
$sourceKeys = ['get', 'header', 'default'];

// Yii 2
$context = new Yii2Context($config);
$detector = new LanguageDetector($context, $sourceKeys, $config);

// Laravel - extend ServiceProvider and pass to constructor
// Symfony - configure in services.yaml
```

---

## 🗃️ Example Language Table

```sql
CREATE TABLE `language` (
  `code` VARCHAR(5) NOT NULL,
  `short_name` VARCHAR(3) NOT NULL,
  `full_name` VARCHAR(32) NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT '1',
  `order` TINYINT NOT NULL,
  PRIMARY KEY (`code`))
ENGINE = InnoDB
```

Sample data:

```sql
INSERT INTO language (code, is_enabled, `order`)
VALUES
  ('en', 1, 1),
  ('uk', 1, 2),
  ('ru', 0, 3);
```

## 🧪 Running Tests

Install PHPUnit as a dev dependency:

```bash
composer require --dev phpunit/phpunit
```
Run the test suite:
```bash
./vendor/bin/phpunit -c phpunit.xml.dist
```

Or define a shortcut in composer.json:
```json
"scripts": {
    "test": "phpunit -c phpunit.xml.dist"
}
```

Then simply run:
```bash
composer test
```


## 📁 Directory Structure

```css
language-detector/
│   src/
│   ├── Application/
│   │   ├── LanguageDetector.php
│   │   └── SourceFactory.php
│   ├── Domain/
│   │   ├── Contracts/
│   │   │   ├── FrameworkContextInterface.php   // namespace LanguageDetector\Domain\Contracts
│   │   │   ├── RequestInterface.php         
│   │   │   ├── ResponseInterface.php
│   │   │   ├── UserInterface.php
│   │   │   ├── SourceInterface.php
│   │   │   ├── LanguageRepositoryInterface.php
│   │   │   └── EventDispatcherInterface.php
│   │   ├── Events/
│   │   │   └── LanguageChangedEvent.php        // namespace LanguageDetector\Domain\Events
│   │   └── Sources/
│   │       ├── PathSource.php                  // namespace LanguageDetector\Domain\Sources
│   │       ├── PostSource.php
│   │       ├── GetSource.php
│   │       ├── UserProfileSource.php
│   │       ├── SessionSource.php
│   │       ├── CookieSource.php
│   │       ├── HeaderSource.php
│   │       └── DefaultSource.php
│   └── Infrastructure/
│       └── Adapters/
│           ├── Yii2/
│           │   ├── Bootstrap.php
│           │   ├── Yii2Context.php
│           │   ├── YiiRequestAdapter.php               // implements RequestInterface
│           │   ├── YiiResponseAdapter.php              // implements ResponseInterface
│           │   ├── YiiUserAdapter.php                  // implements UserInterface
│           │   ├── YiiCacheAdapter.php                 // implements CacheInterface
│           │   ├── YiiLanguageRepository.php           // implements LanguageRepositoryInterface
│           │   └── YiiEventDispatcher.php              // implements EventDispatcherInterface
│           ├── Yii3/
│           │   ├── LanguageMiddleware.php
│           │   ├── Yii3Context.php
│           │   ├── Yii3RequestAdapter.php              // implements RequestInterface
│           │   ├── Yii3ResponseAdapter.php             // implements ResponseInterface
│           │   ├── Yii3UserAdapter.php                 // implements UserInterface
│           │   ├── Yii3CacheAdapter.php                // implements CacheInterface
│           │   ├── Yii3LanguageRepository.php          // implements LanguageRepositoryInterface
│           │   └── Yii3EventDispatcher.php             // implements EventDispatcherInterface
│           ├── Symfony/
│           │   ├── RequestListener.php
│           │   ├── SymfonyContext.php
│           │   ├── SymfonyRequestAdapter.php           // implements RequestInterface
│           │   ├── SymfonyResponseAdapter.php          // implements ResponseInterface
│           │   ├── SymfonyUserAdapter.php              // implements UserInterface
│           │   ├── SymfonyCacheAdapter.php             // implements CacheInterface
│           │   ├── SymfonyLanguageRepository.php       // implements LanguageRepositoryInterface
│           │   └── SymfonyEventDispatcher.php          // implements EventDispatcherInterface
│           └── Laravel/
│               ├── LanguageDetectorServiceProvider.php
│               ├── LaravelMiddleware.php
│               ├── LaravelContext.php
│               ├── LaravelRequestAdapter.php           // implements RequestInterface
│               ├── LaravelResponseAdapter.php          // implements ResponseInterface
│               ├── LaravelUserAdapter.php              // implements UserInterface
│               ├── LaravelCacheAdapter.php             // implements CacheInterface
│               ├── LaravelLanguageRepository.php       // implements LanguageRepositoryInterface
│               └── LaravelEventDispatcher.php          // implements EventDispatcherInterface
├── tests
│   └── TestLanguageDetector.php
composer.json
phpunit.xml.dist
LICENSE
```

### 🧩 DDD Architecture Layers

**Domain Layer** (`src/Domain/`):
- **Contracts** — interfaces defining core abstractions (RequestInterface, UserInterface, FrameworkContextInterface, etc.)
- **Events** — domain events (LanguageChangedEvent)
- **Sources** — language detection sources (PostSource, GetSource, PathSource, UserProfileSource, etc.)

**Application Layer** (`src/Application/`):
- **LanguageDetector** — main service orchestrating language detection
- **SourceFactory** — factory for creating source instances

**Infrastructure Layer** (`src/Infrastructure/Adapters/`):
- Framework-specific implementations (Yii2, Laravel, Symfony)
- Each adapter implements `FrameworkContextInterface` providing access to framework services
- Adapters are isolated from business logic and can be easily swapped

## 🧰 Example Test

Running the included test file:

```bash
php tests/TestLanguageDetector.php
```

Sample output:
```
=== Language Detector Tests ===

Test 1 - Path (/en/test): ✓ PASS
Test 2 - GET parameter (lang=uk): ✓ PASS
Test 3 - POST parameter (lang=fr): ✓ PASS
Test 4 - Cookie (lang=de): ✓ PASS
Test 5 - Session (lang=uk): ✓ PASS
Test 6 - User profile (language_code=fr): ✓ PASS
Test 7 - Accept-Language header (de-DE,de;q=0.9,en;q=0.8): ✓ PASS
Test 8 - Default fallback: ✓ PASS
Test 9 - Invalid language (lang=invalid): ✓ PASS
Test 10 - Cache stores enabled languages: ✓ PASS

=== Tests Complete ===
```

The test file demonstrates how to create mock implementations of all required interfaces and test the detector in isolation.


## 📄 License

Released under the MIT License
© 2025 Oleksandr Nosov