# CLAUDE.md - AI Assistant Guide for Language Detector

**Last Updated**: 2025-11-22
**Repository**: alex-no/language-detector
**Version**: 1.1.3+
**License**: MIT

---

## Repository Overview

This is a **framework-agnostic PHP 8.0+ language detection library** with official adapters for **Yii2**, **Laravel**, and **Symfony**. The library follows a clean **DDD-inspired architecture** (Domain-Driven Design) with three distinct layers.

### Core Purpose
Detects user's preferred language from multiple sources (URL, POST, GET, cookies, session, user profile, Accept-Language header) with configurable priority order, validates against enabled languages from database, and persists the choice.

### Key Statistics
- **Language**: PHP 8.0+
- **Architecture**: Domain-Driven Design (3 layers)
- **Supported Frameworks**: Yii2, Laravel, Symfony
- **Dependencies**: PSR-16 (SimpleCache), PSR-14 (EventDispatcher)
- **Test Coverage**: Integration tests with PHPUnit
- **CI/CD**: GitHub Actions (PHP 8.0, 8.1, 8.2)

---

## Architecture & Design Patterns

### Three-Layer DDD Structure

```
┌─────────────────────────────────────────────────────┐
│              APPLICATION LAYER                       │
│  ┌────────────────────┐  ┌──────────────────────┐  │
│  │ LanguageDetector   │  │   SourceFactory      │  │
│  │ (Orchestrator)     │  │   (Factory Pattern)  │  │
│  └────────────────────┘  └──────────────────────┘  │
└─────────────────────────────────────────────────────┘
                         ▲
                         │ uses
                         ▼
┌─────────────────────────────────────────────────────┐
│               DOMAIN LAYER (Pure Logic)              │
│  ┌──────────────────────────────────────────────┐  │
│  │ Contracts (7 Interfaces + 1 Event)           │  │
│  │  - FrameworkContextInterface                 │  │
│  │  - RequestInterface, ResponseInterface       │  │
│  │  - UserInterface, SourceInterface            │  │
│  │  - LanguageRepositoryInterface               │  │
│  │  - EventDispatcherInterface                  │  │
│  └──────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────┐  │
│  │ Sources (8 Detection Strategies)             │  │
│  │  PostSource, GetSource, PathSource           │  │
│  │  UserProfileSource, SessionSource            │  │
│  │  CookieSource, HeaderSource, DefaultSource   │  │
│  └──────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────┐  │
│  │ Events: LanguageChangedEvent                 │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
                         ▲
                         │ implements
                         ▼
┌─────────────────────────────────────────────────────┐
│          INFRASTRUCTURE LAYER (Adapters)             │
│  ┌────────────┐  ┌────────────┐  ┌──────────────┐  │
│  │    Yii2    │  │  Laravel   │  │   Symfony    │  │
│  │ (8 files)  │  │ (8 files)  │  │  (8 files)   │  │
│  └────────────┘  └────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────┘
```

### Design Patterns Used

1. **Adapter Pattern**: Framework-specific implementations adapt to domain interfaces
2. **Strategy Pattern**: Language sources are interchangeable detection strategies
3. **Factory Pattern**: `SourceFactory` creates source instances dynamically
4. **Repository Pattern**: `LanguageRepositoryInterface` abstracts database access
5. **Dependency Injection**: Constructor-based DI throughout
6. **Chain of Responsibility**: Sources are tried in priority order until one succeeds

---

## Directory Structure

```
language-detector/
├── .github/
│   └── workflows/
│       └── run-tests.yml          # CI/CD pipeline (PHP 8.0-8.2)
├── src/
│   ├── Application/
│   │   ├── LanguageDetector.php   # Main orchestrator (~290 lines)
│   │   └── SourceFactory.php      # Creates source instances (~60 lines)
│   ├── Domain/
│   │   ├── Contracts/             # Framework-agnostic interfaces
│   │   │   ├── FrameworkContextInterface.php
│   │   │   ├── RequestInterface.php
│   │   │   ├── ResponseInterface.php
│   │   │   ├── UserInterface.php
│   │   │   ├── SourceInterface.php
│   │   │   ├── LanguageRepositoryInterface.php
│   │   │   └── EventDispatcherInterface.php
│   │   ├── Events/
│   │   │   └── LanguageChangedEvent.php
│   │   └── Sources/               # 8 detection strategies
│   │       ├── PostSource.php     # Priority 1: POST parameter
│   │       ├── GetSource.php      # Priority 2: GET parameter
│   │       ├── PathSource.php     # Priority 3: URL path segment
│   │       ├── UserProfileSource.php  # Priority 4: User attribute
│   │       ├── SessionSource.php  # Priority 5: Session storage
│   │       ├── CookieSource.php   # Priority 6: Cookie
│   │       ├── HeaderSource.php   # Priority 7: Accept-Language
│   │       └── DefaultSource.php  # Priority 8: Fallback
│   └── Infrastructure/
│       └── Adapters/
│           ├── Yii2/              # Yii 2 framework integration
│           │   ├── Bootstrap.php
│           │   ├── Yii2Context.php
│           │   ├── YiiRequestAdapter.php
│           │   ├── YiiResponseAdapter.php
│           │   ├── YiiUserAdapter.php
│           │   ├── YiiCacheAdapter.php
│           │   ├── YiiLanguageRepository.php
│           │   └── YiiEventDispatcher.php
│           ├── Laravel/           # Laravel framework integration
│           │   ├── LaravelServiceProvider.php
│           │   ├── LaravelContext.php
│           │   ├── LaravelRequestAdapter.php
│           │   ├── LaravelResponseAdapter.php
│           │   ├── LaravelUserAdapter.php
│           │   ├── LaravelCacheAdapter.php
│           │   ├── LaravelLanguageRepository.php
│           │   └── LaravelEventDispatcher.php
│           └── Symfony/           # Symfony framework integration
│               ├── RequestListener.php
│               ├── SymfonyContext.php
│               ├── SymfonyRequestAdapter.php
│               ├── SymfonyResponseAdapter.php
│               ├── SymfonyUserAdapter.php
│               ├── SymfonyCacheAdapter.php
│               ├── SymfonyLanguageRepository.php
│               └── SymfonyEventDispatcher.php
├── tests/
│   └── TestLanguageDetector.php   # Integration tests
├── .gitignore
├── composer.json
├── phpunit.xml.dist
├── LICENSE
└── README.md
```

---

## Core Concepts

### Language Detection Flow

```
User Request → detect(isApi)
    ↓
┌───────────────────────────────────────┐
│ 1. Check if console → return default │
└───────────────────────────────────────┘
    ↓
┌───────────────────────────────────────┐
│ 2. Iterate sources in priority order: │
│    - PostSource (lang param in POST)  │
│    - GetSource (lang param in GET)    │
│    - PathSource (URL segment)         │
│    - UserProfileSource (user attr)    │
│    - SessionSource (session storage)  │
│    - CookieSource (cookie)            │
│    - HeaderSource (Accept-Language)   │
│    - DefaultSource (fallback)         │
└───────────────────────────────────────┘
    ↓
┌───────────────────────────────────────┐
│ 3. For each source:                   │
│    - Call getLanguage()               │
│    - Validate against enabled langs   │
│    - If valid → finalize() and return │
│    - If invalid → try next source     │
└───────────────────────────────────────┘
    ↓
┌───────────────────────────────────────┐
│ 4. finalize(language):                │
│    - Set session                      │
│    - Set cookie (1 year expiry)       │
│    - Update user attribute if auth    │
│    - Dispatch LanguageChangedEvent    │
└───────────────────────────────────────┘
    ↓
Return detected language (2-letter code)
```

### Source Priority System

**Default Priority Order** (customizable):
1. **POST** - Highest priority for explicit user selection
2. **GET** - URL query parameter
3. **PATH** - URL path segment (e.g., `/en/products`)
4. **USER PROFILE** - Authenticated user's saved preference
5. **SESSION** - Current session (web only)
6. **COOKIE** - Persistent cookie (web only)
7. **HEADER** - Browser Accept-Language header
8. **DEFAULT** - Always succeeds with fallback language

**Priority Customization**:
```php
// Custom order example
$detector = new LanguageDetector(
    $context,
    ['header', 'user', 'default'], // Only use header, user, and default
    $config
);
```

### Language Validation Process

```php
extractValidLang($input)
    ↓
┌─────────────────────────────────────┐
│ Input can be:                       │
│  - string: 'en'                     │
│  - array: ['en', 'fr']              │
│  - Accept-Language: 'en;q=0.8,fr'  │
└─────────────────────────────────────┘
    ↓
Parse and normalize to 2-letter codes
    ↓
Check against getAllowedLanguages()
    ↓
Return first valid language or null
```

**Allowed Languages Caching**:
```
getAllowedLanguages()
    ↓
Check cache (key: 'allowed_languages', TTL: 3600s)
    ↓
Cache hit? → Return cached array
    ↓
Cache miss? → Query database
    ↓
SELECT code FROM language
WHERE is_enabled = 1
ORDER BY order
    ↓
Cache result and return
```

### Event Dispatching

**LanguageChangedEvent** is dispatched when:
- User is authenticated (not guest)
- Detected language differs from user's current attribute
- User attribute is successfully updated and saved

**Event Properties**:
```php
class LanguageChangedEvent {
    public readonly string $oldLanguage;  // Previous language code
    public readonly string $newLanguage;  // New language code
    public readonly ?UserInterface $user; // User instance
}
```

---

## Development Practices

### PHP Standards

**Version**: PHP 8.0+ required

**Code Style Features**:
- `declare(strict_types=1);` on every file
- Full type hints for parameters and returns
- Constructor property promotion: `public function __construct(private string $param)`
- Readonly properties where appropriate
- Union types: `string|array|null`
- Match expressions for conditional logic
- Named arguments support

**PSR Compliance**:
- PSR-4: Autoloading (`LanguageDetector\` namespace)
- PSR-16: SimpleCache interface
- PSR-14: EventDispatcher interface

### Error Handling Pattern

All operations use defensive programming:

```php
try {
    // Attempt operation
    return $result;
} catch (\Throwable) {
    // Silent failure - return safe default
    return null; // or false, or $default
}
```

**Philosophy**: Never break the application. If language detection fails, fall back gracefully to default language.

### Naming Conventions

- **Classes**: PascalCase (e.g., `LanguageDetector`, `YiiRequestAdapter`)
- **Methods**: camelCase (e.g., `getLanguage()`, `extractValidLang()`)
- **Properties**: camelCase with visibility (e.g., `private string $paramName`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `DEFAULT_CONFIG`, `COOKIE_LIFETIME`)
- **Interfaces**: PascalCase + `Interface` suffix (e.g., `RequestInterface`)
- **Namespaces**: PSR-4 structure matching directory hierarchy

### Code Organization Principles

1. **Interface-First Design**: Define contracts in Domain, implement in Infrastructure
2. **No Framework Dependencies in Domain**: Core logic is framework-agnostic
3. **Constructor Injection**: All dependencies passed via constructor
4. **Immutability**: Use `readonly` for properties that shouldn't change
5. **Single Responsibility**: Each class has one clear purpose
6. **Final Classes**: Most implementations are `final` to prevent inheritance issues

---

## Configuration Reference

### Standard Configuration Options

```php
[
    // Parameter name used in POST/GET/Session/Cookie/User attribute
    'paramName' => 'lang',

    // Default fallback language code (2 letters)
    'default' => 'en',

    // Cache key for storing allowed languages list
    'cacheKey' => 'allowed_languages',

    // Cache TTL in seconds (default: 1 hour)
    'cacheTtl' => 3600,

    // URL path segment index for PathSource (0-based)
    // Example: /en/products → index 0 extracts 'en'
    'pathSegmentIndex' => 0,

    // User model attribute name for language preference
    'userAttribute' => 'language_code',

    // Database table configuration
    'tableName' => 'language',
    'codeField' => 'code',
    'enabledField' => 'is_enabled',
    'orderField' => 'order',
]
```

### Database Schema

**Required Table Structure**:
```sql
CREATE TABLE `language` (
  `code` VARCHAR(5) NOT NULL,           -- Language code (e.g., 'en', 'uk', 'fr')
  `short_name` VARCHAR(3) NOT NULL,     -- Optional: short display name
  `full_name` VARCHAR(32) NOT NULL,     -- Optional: full display name
  `is_enabled` TINYINT(1) NOT NULL DEFAULT '1',  -- Enable/disable flag
  `order` TINYINT NOT NULL,             -- Sort order for priority
  PRIMARY KEY (`code`)
) ENGINE = InnoDB;
```

**Sample Data**:
```sql
INSERT INTO language (code, short_name, full_name, is_enabled, `order`)
VALUES
  ('en', 'EN', 'English', 1, 1),
  ('uk', 'UA', 'Ukrainian', 1, 2),
  ('fr', 'FR', 'French', 1, 3),
  ('ru', 'RU', 'Russian', 0, 4);  -- Disabled
```

**Field Customization**: All field names are configurable via config array.

---

## Testing Guidelines

### Running Tests

```bash
# Via Composer script
composer test

# Direct PHPUnit invocation
./vendor/bin/phpunit -c phpunit.xml.dist

# With verbose output
./vendor/bin/phpunit -c phpunit.xml.dist -v

# With coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### Test Structure

**Location**: `tests/TestLanguageDetector.php`

**Approach**: Integration testing with mock objects

**Test Coverage**:
- Path source detection
- GET parameter detection
- POST parameter detection
- Cookie detection
- Session detection
- User profile detection
- Accept-Language header parsing

### CI/CD Pipeline

**GitHub Actions** (`.github/workflows/run-tests.yml`):
- Triggers: Push to main, Pull requests
- PHP versions tested: 8.0, 8.1, 8.2
- Extensions: mbstring, pdo, pdo_mysql
- Steps: Checkout → Setup PHP → Install dependencies → Run tests

**Adding Tests**:
1. Create test file in `tests/` directory
2. Name must match `*Test.php` pattern
3. Extend `PHPUnit\Framework\TestCase`
4. Use dummy objects implementing domain interfaces
5. Test the public `detect()` method with different source combinations

---

## Framework-Specific Integration

### Yii2 Integration

**Configuration** (`config/web.php`):
```php
'bootstrap' => [
    'languageBootstrap',
],
'components' => [
    'languageBootstrap' => [
        'class' => \LanguageDetector\Infrastructure\Adapters\Yii2\Bootstrap::class,
        'detectorClass' => \LanguageDetector\Application\LanguageDetector::class,
        'paramName' => 'lang',
        'default' => 'en',
        'userAttribute' => 'language_code',
        'tableName' => 'language',
        'codeField' => 'code',
        'enabledField' => 'is_enabled',
        'pathSegmentIndex' => 1,
    ],
],
```

**Usage**:
```php
// Automatic detection on bootstrap
Yii::$app->language; // Already set to detected language

// Manual detection
Yii::$app->languageBootstrap->apply();

// Event listener
Yii::$app->on('language.changed', function($event) {
    // $event->oldLanguage
    // $event->newLanguage
    // $event->user
});
```

**Key Files**:
- `Bootstrap.php`: Implements `yii\base\BootstrapInterface`
- `Yii2Context.php`: Creates all adapters lazily
- `YiiRequestAdapter.php`: Complex path extraction with 3 fallbacks
- `YiiUserAdapter.php`: Reflection-based save() detection

### Laravel Integration

**Service Provider Registration** (`config/app.php`):
```php
'providers' => [
    LanguageDetector\Infrastructure\Adapters\Laravel\LaravelServiceProvider::class,
],
```

**Middleware Registration** (`app/Http/Kernel.php`):
```php
protected $middlewareGroups = [
    'web' => [
        \LanguageDetector\Infrastructure\Adapters\Laravel\LaravelMiddleware::class,
    ],
];
```

**Configuration** (`config/language.php`):
```php
return [
    'paramName' => 'lang',
    'default' => 'en',
    'userAttribute' => 'language_code',
    'tableName' => 'language',
    'codeField' => 'code',
    'enabledField' => 'is_enabled',
];
```

**Usage**:
```php
// Dependency injection
public function index(LanguageDetector $detector)
{
    $lang = $detector->detect();
    App::setLocale($lang);

    return view('welcome', ['lang' => $lang]);
}

// Event listener
Event::listen(LanguageChangedEvent::class, function($event) {
    // Handle language change
});
```

**Key Files**:
- `LaravelServiceProvider.php`: Registers singleton, sets locale on boot
- `LaravelRequestAdapter.php`: Uses `$request->path()` for routing
- `LaravelResponseAdapter.php`: Converts Unix timestamp to minutes for cookies

### Symfony Integration

**Service Registration** (`config/services.yaml`):
```yaml
services:
    LanguageDetector\Infrastructure\Adapters\Symfony\RequestListener:
        tags:
            - { name: kernel.event_listener, event: kernel.request }
```

**Usage**:
```php
// Automatic detection via RequestListener
// Language set on every request via $request->setLocale()

// Event listener
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LanguageDetector\Domain\Events\LanguageChangedEvent;

class LanguageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            LanguageChangedEvent::class => 'onLanguageChanged',
        ];
    }

    public function onLanguageChanged(LanguageChangedEvent $event)
    {
        // Handle language change
    }
}
```

**Key Files**:
- `RequestListener.php`: Kernel event listener for automatic detection
- `SymfonyContext.php`: Constructor-based dependency injection
- `SymfonyCacheAdapter.php`: Most complex adapter (handles 2 cache contracts)

---

## Common Tasks for AI Assistants

### Adding a New Language Source

**Steps**:
1. Create new source class in `src/Domain/Sources/NewSource.php`
2. Implement `SourceInterface`:
   ```php
   class NewSource implements SourceInterface
   {
       public function getLanguage(
           RequestInterface $request,
           ?UserInterface $user,
           bool $isApi
       ): string|array|null {
           // Your detection logic
           return $languageCode; // or null if not found
       }

       public function getKey(): string
       {
           return 'new_source';
       }
   }
   ```
3. Register in `SourceFactory::getMap()`:
   ```php
   'new_source' => new NewSource($config['someParam']),
   ```
4. Add to default source keys or use in custom priority order
5. Write tests in `tests/TestLanguageDetector.php`

### Adding a New Framework Adapter

**Required Files** (8 total):
1. `NewFrameworkContext.php` - Implements `FrameworkContextInterface`
2. `NewFrameworkRequestAdapter.php` - Implements `RequestInterface`
3. `NewFrameworkResponseAdapter.php` - Implements `ResponseInterface`
4. `NewFrameworkUserAdapter.php` - Implements `UserInterface`
5. `NewFrameworkCacheAdapter.php` - Implements PSR-16 `CacheInterface`
6. `NewFrameworkLanguageRepository.php` - Implements `LanguageRepositoryInterface`
7. `NewFrameworkEventDispatcher.php` - Implements `EventDispatcherInterface`
8. Bootstrap/ServiceProvider/Listener class for framework integration

**Directory**: `src/Infrastructure/Adapters/NewFramework/`

**Context Implementation Pattern**:
```php
final class NewFrameworkContext implements FrameworkContextInterface
{
    public function getRequest(): RequestInterface
    {
        return new NewFrameworkRequestAdapter(/* framework request */);
    }

    public function getResponse(): ResponseInterface
    {
        return new NewFrameworkResponseAdapter(/* framework response */);
    }

    // Implement remaining 5 methods...
}
```

### Modifying Language Validation Logic

**File**: `src/Application/LanguageDetector.php`

**Method**: `extractValidLang(string|array|null $input): ?string`

**Current Logic**:
1. Handle null input
2. Parse Accept-Language header if applicable
3. Normalize to 2-letter codes
4. Validate against allowed languages
5. Return first valid or null

**Extension Example**:
```php
private function extractValidLang(string|array|null $input): ?string
{
    if ($input === null) {
        return null;
    }

    // Custom parsing logic here
    $languages = $this->customParseLanguages($input);

    $allowed = $this->getAllowedLanguages();

    foreach ($languages as $lang) {
        if (in_array($lang, $allowed, true)) {
            return $lang;
        }
    }

    return null;
}
```

### Changing Cache Strategy

**File**: `src/Application/LanguageDetector.php`

**Methods**:
- `getAllowedLanguages(): array` - Retrieves from cache or DB
- `refreshAllowedLanguages(): array` - Queries DB and caches

**Custom TTL**:
```php
// Via configuration
$config = [
    'cacheTtl' => 7200, // 2 hours instead of default 1 hour
];
```

**Cache Invalidation**:
```php
// Clear cache when languages are updated
$cache->delete('allowed_languages');
// Or use custom cache key
$config = ['cacheKey' => 'custom_lang_cache'];
```

### Adding Custom Validation Rules

**Location**: `src/Application/LanguageDetector.php`

**Current Validation**: Checks if language code is in enabled languages list

**Extension Point**:
```php
private function isValidLanguage(string $code): bool
{
    // Current logic
    $allowed = $this->getAllowedLanguages();
    if (!in_array($code, $allowed, true)) {
        return false;
    }

    // Add custom validation
    if (strlen($code) !== 2) {
        return false; // Only 2-letter codes
    }

    if (!ctype_alpha($code)) {
        return false; // Only alphabetic characters
    }

    return true;
}
```

### Debugging Detection Issues

**Enable Verbose Logging**:
```php
// Add logging to detect() method
private function detect(bool $isApi = false): string
{
    error_log('Language detection started, isApi: ' . ($isApi ? 'true' : 'false'));

    foreach ($this->sources as $source) {
        $lang = $source->getLanguage($this->context->getRequest(), $user, $isApi);
        error_log("Source {$source->getKey()} returned: " . var_export($lang, true));

        // Continue with validation...
    }
}
```

**Common Issues**:
1. **Always returns default**: Check if enabled languages are cached/queryable
2. **Source not working**: Verify source is in `sourceKeys` array
3. **Cookie not persisting**: Check `ResponseInterface` implementation
4. **Event not firing**: Ensure user is authenticated and language actually changed

---

## Important Behavioral Notes

### Console Request Handling
- `detect()` immediately returns default language for console requests
- Check: `$request->isConsole()` at start of detection
- Prevents unnecessary processing for CLI commands

### API vs Web Context
- `isApi` parameter controls session/cookie behavior
- When `isApi = true`:
  - `SessionSource` returns null (skipped)
  - `CookieSource` returns null (skipped)
  - Only POST, GET, Path, User, Header, Default sources used
- Use `detect(true)` for API endpoints, `detect(false)` for web pages

### Guest User Handling
- `UserProfileSource` returns null when `$user->isGuest()` is true
- Language changes NOT saved to user profile for guests
- `LanguageChangedEvent` NOT dispatched for guests
- Session and cookie still work for guest language preferences

### Language Normalization
- All language codes normalized to **2-letter lowercase** (e.g., 'en', 'fr', 'uk')
- Accept-Language header parsing extracts primary subtag: `en-US` → `en`
- Validation only checks 2-letter codes against database

### Cache Behavior
- Allowed languages cached for 3600 seconds (1 hour) by default
- Cache key: `'allowed_languages'` (configurable)
- Cache miss triggers database query via `LanguageRepositoryInterface`
- Framework adapters use their native cache implementations (Yii2, Laravel, Symfony)

### Error Tolerance
- All operations wrapped in try-catch blocks catching `\Throwable`
- Exceptions are silently caught, returning safe defaults
- Philosophy: Never break application due to language detection failure
- Always falls back to default language as last resort

### Accept-Language Header Parsing
- Supports quality values: `en;q=0.8, fr;q=0.9, uk;q=1.0`
- Higher quality values have priority
- Default quality: 1.0 (when not specified)
- Parses multiple languages and selects highest quality valid language
- Normalizes locale codes: `en-US;q=0.9` → `en` with quality 0.9

### Cookie Lifetime
- Cookies set with 1-year expiration: `3600 * 24 * 365` seconds
- Configurable via constant `COOKIE_LIFETIME` in adapters
- Uses Unix timestamp for expiration calculation

---

## Extension Points Summary

### Easy Customizations (No Code Changes)
1. **Priority Order**: Pass custom `sourceKeys` array to constructor
2. **Configuration**: Modify config array (paramName, default, cacheTtl, etc.)
3. **Database Schema**: Configure table and field names via config
4. **Cache Duration**: Set `cacheTtl` in config
5. **URL Path Segment**: Set `pathSegmentIndex` for PathSource

### Medium Complexity (Extend Core Classes)
1. **Custom Source**: Implement `SourceInterface`, register in `SourceFactory`
2. **Custom Validation**: Override `extractValidLang()` or add validation logic
3. **Custom Event Handling**: Add listeners for `LanguageChangedEvent`
4. **Custom Cache Strategy**: Override `getAllowedLanguages()` method

### Complex Extensions (New Framework Support)
1. **New Framework Adapter**: Create 8 adapter files implementing domain interfaces
2. **Alternative Database**: Implement `LanguageRepositoryInterface` for NoSQL, API, etc.
3. **Alternative Cache**: Implement PSR-16 `CacheInterface` for Redis, Memcached, etc.

---

## Key Files Reference

### Core Application Files
- **src/Application/LanguageDetector.php** - Main orchestrator, ~290 lines
- **src/Application/SourceFactory.php** - Creates source instances, ~60 lines

### Domain Contracts (Interfaces)
- **src/Domain/Contracts/FrameworkContextInterface.php** - Main DI interface
- **src/Domain/Contracts/RequestInterface.php** - Request abstraction
- **src/Domain/Contracts/ResponseInterface.php** - Response abstraction (cookies)
- **src/Domain/Contracts/UserInterface.php** - User/authentication abstraction
- **src/Domain/Contracts/SourceInterface.php** - Language source strategy
- **src/Domain/Contracts/LanguageRepositoryInterface.php** - Database abstraction
- **src/Domain/Contracts/EventDispatcherInterface.php** - Event dispatching (PSR-14)

### Language Sources (Strategies)
- **src/Domain/Sources/PostSource.php** - Reads POST parameter
- **src/Domain/Sources/GetSource.php** - Reads GET parameter
- **src/Domain/Sources/PathSource.php** - Extracts from URL path
- **src/Domain/Sources/UserProfileSource.php** - Reads user attribute
- **src/Domain/Sources/SessionSource.php** - Reads session
- **src/Domain/Sources/CookieSource.php** - Reads cookie
- **src/Domain/Sources/HeaderSource.php** - Parses Accept-Language
- **src/Domain/Sources/DefaultSource.php** - Returns fallback

### Events
- **src/Domain/Events/LanguageChangedEvent.php** - Dispatched on user language change

### Tests
- **tests/TestLanguageDetector.php** - Integration tests with mocks
- **phpunit.xml.dist** - PHPUnit configuration

### Configuration
- **composer.json** - Package metadata, autoloading, dependencies
- **.github/workflows/run-tests.yml** - CI/CD pipeline

---

## Quick Reference: Method Signatures

### LanguageDetector (Main Class)

```php
class LanguageDetector
{
    // Constructor
    public function __construct(
        FrameworkContextInterface $context,
        ?array $sourceKeys = null,
        array $config = []
    )

    // Main detection method
    public function detect(bool $isApi = false): string

    // Get allowed languages (with caching)
    public function getAllowedLanguages(): array

    // Refresh cache from database
    public function refreshAllowedLanguages(): array

    // Extract valid language from input
    private function extractValidLang(string|array|null $input): ?string

    // Parse Accept-Language header
    private function parseAcceptLanguageHeader(string $header): array

    // Finalize detection (set session, cookie, user, dispatch event)
    private function finalize(string $lang, bool $isApi): string
}
```

### SourceInterface (All Sources)

```php
interface SourceInterface
{
    // Detect language from source
    public function getLanguage(
        RequestInterface $request,
        ?UserInterface $user,
        bool $isApi
    ): string|array|null;

    // Get unique source identifier
    public function getKey(): string;
}
```

### RequestInterface (Request Abstraction)

```php
interface RequestInterface
{
    public function isConsole(): bool;
    public function get(string $name): mixed;
    public function post(string $name): mixed;
    public function hasHeader(string $name): bool;
    public function getHeader(string $name): ?string;
    public function hasCookie(string $name): bool;
    public function getCookie(string $name): ?string;
    public function hasSession(string $name): bool;
    public function getSession(string $name): mixed;
    public function setSession(string $name, $value): void;
    public function getPath(): ?string;
}
```

### UserInterface (User Abstraction)

```php
interface UserInterface
{
    public function isGuest(): bool;
    public function getAttribute(string $name): mixed;
    public function setAttribute(string $name, $value): void;
    public function saveAttributes(array $names): void;
}
```

---

## Contribution Guidelines for AI Assistants

### When Modifying Core Logic
1. **Preserve backward compatibility** - Don't break existing framework integrations
2. **Maintain interface contracts** - Any interface changes require updating all adapters
3. **Add tests** - New features must include test cases
4. **Update documentation** - Keep README.md and this file in sync
5. **Follow PSR standards** - PSR-4 autoloading, PSR-16 caching, PSR-14 events

### When Adding Features
1. **Domain-first design** - Add interfaces to Domain layer first
2. **Framework-agnostic** - Core logic should not depend on specific frameworks
3. **Configuration over code** - Prefer configurable options over hardcoded values
4. **Silent failures** - Catch exceptions, return safe defaults, never break the app
5. **Type safety** - Use strict types, full type hints, readonly where applicable

### When Fixing Bugs
1. **Identify the layer** - Domain bug vs Infrastructure bug vs Application bug
2. **Add regression test** - Reproduce bug in test, then fix
3. **Consider all frameworks** - Bug fix might affect Yii2, Laravel, AND Symfony
4. **Check cache implications** - Language caching might mask or cause issues
5. **Verify event dispatching** - Ensure events still fire correctly after fix

### Code Review Checklist
- [ ] `declare(strict_types=1);` on all PHP files
- [ ] Full type hints (parameters and returns)
- [ ] Exception handling with `\Throwable` catch
- [ ] No framework-specific code in Domain layer
- [ ] All public methods documented
- [ ] Tests updated/added
- [ ] README.md updated if public API changed
- [ ] CLAUDE.md updated if architecture changed
- [ ] Backward compatible with existing configs

---

## Common Pitfalls & Solutions

### Pitfall: Language Always Returns Default
**Cause**: Enabled languages cache is empty or database query failing
**Solution**:
- Check database connection in LanguageRepository
- Verify `is_enabled = 1` records exist
- Clear cache: `$cache->delete('allowed_languages')`
- Check cache adapter implementation

### Pitfall: Custom Source Not Working
**Cause**: Source not registered in SourceFactory or not in sourceKeys array
**Solution**:
- Add to `SourceFactory::getMap()`
- Include source key in constructor: `new LanguageDetector($context, ['your_source', 'default'])`

### Pitfall: Cookie Not Persisting
**Cause**: ResponseInterface implementation not correctly adding cookie to response
**Solution**:
- Verify `ResponseInterface::addCookie()` implementation
- Check cookie domain/path configuration in framework
- Ensure response is sent to browser (not buffered/discarded)

### Pitfall: Event Not Firing
**Cause**: Event only fires for authenticated users with changed language
**Solution**:
- Verify user is authenticated: `!$user->isGuest()`
- Ensure language actually changed (old ≠ new)
- Check EventDispatcherInterface implementation
- Add event listener before detection runs

### Pitfall: Path Source Not Extracting Language
**Cause**: Wrong `pathSegmentIndex` configuration
**Solution**:
- URL `/en/products` with index 0 → extracts `en`
- URL `/products/en` with index 1 → extracts `en`
- Check actual URL structure and adjust index

### Pitfall: Accept-Language Header Ignored
**Cause**: Header source has lowest priority (except default)
**Solution**:
- Ensure no higher-priority sources are returning values
- Or customize sourceKeys: `['header', 'default']` to prioritize header

---

## Version History & Migration Notes

### Version 1.1.3+ (Current)
- **Architecture**: DDD-inspired structure (Domain/Application/Infrastructure)
- **Breaking Change**: Reorganized namespaces to DDD structure
- **Migration**: Update namespace imports if upgrading from pre-1.1.3

### Pre-1.1.3
- Flat structure without clear layer separation
- Less framework-agnostic design

### Future Considerations
- **Multi-tenant support**: Per-tenant language tables
- **Dynamic source registration**: Runtime source registration without factory modification
- **GraphQL adapter**: Support for GraphQL API language detection
- **Async support**: Promise-based language detection for async frameworks

---

## Resources & Links

- **Repository**: https://github.com/alex-no/language-detector
- **Packagist**: https://packagist.org/packages/alex-no/language-detector
- **License**: MIT (see LICENSE file)
- **Author**: Oleksandr Nosov <alex@4n.com.ua>
- **PHP Requirements**: >=8.0
- **Dependencies**: psr/simple-cache (^1.0 || ^2.0 || ^3.0)
- **Dev Dependencies**: phpunit/phpunit (^9.5 || ^10.0), squizlabs/php_codesniffer (^3.0)

### Related Documentation
- [PSR-16: Simple Cache](https://www.php-fig.org/psr/psr-16/)
- [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/)
- [PSR-4: Autoloading](https://www.php-fig.org/psr/psr-4/)
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)

---

**End of CLAUDE.md** - Last updated 2025-11-22
