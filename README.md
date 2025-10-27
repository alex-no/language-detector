# Language Detector

A framework-agnostic language detection library for PHP 8.0+  
with adapters for **Yii 2** and **Laravel**.

---

## ✨ Features
- Detects language from multiple sources:
  - `POST` / `GET` parameters
  - Authenticated user profile
  - Session / Cookies
  - `Accept-Language` header
- Caches allowed languages from the database
- Can persist language to session, cookie, and user profile
- Works in both web and API contexts
- Easily extensible for any framework via adapters

---

## ⚙️ Installation

Install via Composer:

```bash
composer require alex-no/language-detector
```

---

## 🧩 Basic Usage

You can use the LanguageDetector class directly in any PHP application:

```php
use LanguageDetector\LanguageDetector;

$detector = new LanguageDetector([
    'paramName' => 'lang',
    'default' => 'en',
]);

$lang = $detector->detect();
echo "Detected language: {$lang}";
```

## 🚀 Usage in Yii 2

Register the component in config/web.php:

```php
'bootstrap' => ['languageBootstrap'],
'components' => [
    'languageBootstrap' => [
        'class' => \LanguageDetector\Adapters\Yii2\Bootstrap::class,
        'paramName' => 'lang',
        'default' => 'en',
        'userAttribute' => 'language_code',
        'tableName' => 'language',
        'codeField' => 'code',
        'enabledField' => 'is_enabled',
    ],
],
```

The component will:
 - Checks for a lang parameter in URL or POST data.
 - If not found, reads language from the authenticated user profile.
 - If still not found, reads from session or cookie.
 - Falls back to browser’s Accept-Language.
 - Updates Yii::$app->language accordingly.

You can also call it manually:
```php
Yii::$app->languageBootstrap->apply();
```

## 🚀 Usage in Laravel

Register the Service Provider
Add this line to the providers array in config/app.php
(if not auto-discovered):

```php
'providers' => [
    LanguageDetector\Adapters\Laravel\LanguageServiceProvider::class,
],
```

Register the Middleware

Add to app/Http/Kernel.php:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \LanguageDetector\Adapters\Laravel\LanguageMiddleware::class,
    ],
];
```

Example configuration (config/language.php)

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

Example usage in controller

```php
public function index(LanguageDetector $detector)
{
    $lang = $detector->detect();
    App::setLocale($lang);

    return view('welcome', ['lang' => $lang]);
}
```

How it works

 - Intercepts incoming requests.
 - Detects preferred language based on request or session.
 - Applies it globally using App::setLocale().


## ⚙️ Configuration Options

| Option          | Description                                 | Default         |
| --------------- | ------------------------------------------- | --------------- |
| `paramName`     | Request parameter name for language         | `lang`          |
| `default`       | Fallback language code                      | `en`            |
| `userAttribute` | User model attribute used to store language | `language_code` |
| `tableName`     | Database table name containing languages    | `language`      |
| `codeField`     | Field name containing language code         | `code`          |
| `enabledField`  | Field name for active/enabled flag          | `is_enabled`    |
| `orderField`    | Field used for sorting languages            | `order`         |

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

Make sure PHPUnit is installed (via Composer):

```bash
composer require alex-no/language-detector
```

or manually run PHPUnit:

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


## 🧱 Directory Structure

```css
src/
├── LanguageDetector.php
├── Adapters/
│   ├── Yii2/
│   │   ├── Bootstrap.php
│   └── Laravel/
│       ├── LanguageServiceProvider.php
│       ├──
tests/
├── LanguageDetectorTest.php
README.md
composer.json
phpunit.xml.dist
LICENSE
```

## 🧰 Example test

A minimal test file tests/LanguageDetectorTest.php:

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use LanguageDetector\LanguageDetector;

final class LanguageDetectorTest extends TestCase
{
    public function testDefaultLanguage(): void
    {
        $detector = new LanguageDetector(['default' => 'en']);
        $this->assertSame('en', $detector->detect());
    }
}
```


## 📄 License

Released under the MIT License
© 2025 Oleksandr Nosov