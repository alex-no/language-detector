# Миграция на PDO-based Repository

Этот документ содержит инструкции по миграции существующего кода после перехода на единый `PdoLanguageRepository`.

## Что изменилось

Все framework-specific репозитории (`YiiLanguageRepository`, `Yii3LanguageRepository`, `LaravelLanguageRepository`, `SymfonyLanguageRepository`) были удалены и заменены единым `PdoLanguageRepository`.

Теперь все адаптеры используют стандартный PDO для доступа к базе данных, что делает библиотеку по-настоящему framework-agnostic.

---

## Yii2: Изменения в вашем коде

### ❌ Старый код (больше не работает):

```php
use LanguageDetector\Infrastructure\Adapters\Yii2\Yii2Context;

// Передавали DB Connection напрямую
$context = new Yii2Context([
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
]);
```

### ✅ Новый код (ничего не меняется!):

```php
use LanguageDetector\Infrastructure\Adapters\Yii2\Yii2Context;

// Код остается точно таким же!
$context = new Yii2Context([
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
]);
```

**Изменения:** НЕТ ИЗМЕНЕНИЙ! Yii2Context внутри автоматически получает PDO из `Yii::$app->db->getMasterPdo()`.

### Дополнительно: Настройка таблицы БД (опционально)

Если вы используете нестандартные имена таблиц/полей, добавьте параметры в конфиг:

```php
$context = new Yii2Context([
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
    // Новые параметры для настройки БД:
    'table' => 'my_languages',           // Имя таблицы (default: 'language')
    'codeField' => 'lang_code',          // Поле кода языка (default: 'code')
    'enabledField' => 'active',          // Поле статуса (default: 'is_enabled')
    'orderField' => 'sort_order',        // Поле сортировки (default: 'order')
]);
```

---

## Laravel: Изменения в вашем коде

### ❌ Старый код (больше не работает):

```php
use LanguageDetector\Infrastructure\Adapters\Laravel\LaravelContext;

$context = new LaravelContext([
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
]);
```

### ✅ Новый код (ничего не меняется!):

```php
use LanguageDetector\Infrastructure\Adapters\Laravel\LaravelContext;

// Код остается точно таким же!
$context = new LaravelContext([
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
]);
```

**Изменения:** НЕТ ИЗМЕНЕНИЙ! LaravelContext внутри автоматически получает PDO из `DB::connection()->getPdo()`.

### Дополнительно: Настройка таблицы БД (опционально)

```php
$context = new LaravelContext([
    'paramName' => 'lang',
    'userAttribute' => 'language_code',
    'default' => 'en',
    // Новые параметры для настройки БД:
    'table' => 'my_languages',
    'codeField' => 'lang_code',
    'enabledField' => 'active',
    'orderField' => 'sort_order',
]);
```

---

## Symfony: Изменения в вашем коде

### ❌ Старый код (может не работать):

```php
use LanguageDetector\Infrastructure\Adapters\Symfony\SymfonyContext;

$context = new SymfonyContext(
    $requestStack,
    $cache,
    $dispatcher,
    $connection,
    [
        'paramName' => 'lang',
        'userAttribute' => 'language_code',
        'default' => 'en',
    ]
);
```

### ✅ Новый код (работает):

```php
use LanguageDetector\Infrastructure\Adapters\Symfony\SymfonyContext;

// Код остается точно таким же!
$context = new SymfonyContext(
    $requestStack,
    $cache,
    $dispatcher,
    $connection,  // Doctrine DBAL Connection
    [
        'paramName' => 'lang',
        'userAttribute' => 'language_code',
        'default' => 'en',
    ]
);
```

**Изменения:** НЕТ ИЗМЕНЕНИЙ! SymfonyContext внутри автоматически получает PDO из `$connection->getNativeConnection()`.

### Дополнительно: Настройка таблицы БД (опционально)

```php
$context = new SymfonyContext(
    $requestStack,
    $cache,
    $dispatcher,
    $connection,
    [
        'paramName' => 'lang',
        'userAttribute' => 'language_code',
        'default' => 'en',
        // Новые параметры для настройки БД:
        'table' => 'my_languages',
        'codeField' => 'lang_code',
        'enabledField' => 'active',
        'orderField' => 'sort_order',
    ]
);
```

---

## Yii3: Изменения в вашем коде

### Вариант 1: Middleware (рекомендуется)

#### ❌ Старый код:
Не применимо - Yii3 адаптер был создан недавно.

#### ✅ Новый код (без изменений):

```php
use LanguageDetector\Infrastructure\Adapters\Yii3\LanguageMiddleware;

// DI конфигурация
return [
    LanguageMiddleware::class => static function (\PDO $pdo, CacheInterface $cache) {
        return new LanguageMiddleware(
            $pdo,
            $cache,
            [
                'paramName' => 'lang',
                'userAttribute' => 'language_code',
                'default' => 'en',
                'pathSegmentIndex' => 0,
                'table' => 'language',
                'codeField' => 'code',
                'enabledField' => 'is_enabled',
                'orderField' => 'order',
            ]
        );
    },
];
```

### Вариант 2: Manual Usage

#### ❌ Старый код (больше не работает):

```php
use LanguageDetector\Infrastructure\Adapters\Yii3\Yii3Context;
use Yiisoft\Db\Connection\ConnectionInterface;

$context = new Yii3Context(
    $config,
    $request,
    $response,
    $identity,
    $cache,
    $eventDispatcher,
    $db  // ConnectionInterface
);
```

#### ✅ Новый код (работает):

```php
use LanguageDetector\Infrastructure\Adapters\Yii3\Yii3Context;
use Yiisoft\Db\Connection\ConnectionInterface;

// Код остается таким же!
$context = new Yii3Context(
    [
        'paramName' => 'lang',
        'userAttribute' => 'language_code',
        'default' => 'en',
    ],
    $request,
    $response,
    $identity,
    $cache,
    $eventDispatcher,
    $db  // ConnectionInterface - PDO извлекается автоматически
);
```

**Изменения:** НЕТ ИЗМЕНЕНИЙ! Yii3Context внутри автоматически получает PDO из `$db->getDriver()->getPDO()`.

---

## Итого: Что нужно изменить?

### Для Yii2:
**НИЧЕГО!** Код работает без изменений.

### Для Laravel:
**НИЧЕГО!** Код работает без изменений.

### Для Symfony:
**НИЧЕГО!** Код работает без изменений.

### Для Yii3:
**НИЧЕГО!** Код работает без изменений.

---

## Преимущества миграции

1. ✅ **Единая кодовая база** - один репозиторий вместо четырех
2. ✅ **Framework-agnostic** - истинная независимость от фреймворков
3. ✅ **Производительность** - прямой PDO быстрее Query Builders
4. ✅ **Меньше кода** - удалено ~200 строк дублированного кода
5. ✅ **Проще тестировать** - одна реализация = меньше тестов
6. ✅ **Гибкость** - теперь можно настроить имена таблиц/полей через конфиг

---

## Устранение проблем

### Ошибка: "Call to undefined method getMasterPdo()" (Yii2)

Убедитесь, что используете Yii2 версии 2.0+. Метод `getMasterPdo()` доступен с этой версии.

### Ошибка: "Call to undefined method getPdo()" (Laravel)

Убедитесь, что используете Laravel версии 5.0+. Метод `getPdo()` доступен с этой версии.

### Ошибка: "Call to undefined method getNativeConnection()" (Symfony)

Убедитесь, что используете Doctrine DBAL версии 3.0+. Для более старых версий используйте:

```php
$pdo = $connection->getWrappedConnection();
```

### Ошибка: "Call to undefined method getPDO()" (Yii3)

Убедитесь, что используете Yii3 с актуальной версией `yiisoft/db`. Метод `getDriver()->getPDO()` доступен в последних версиях.

---

## Дополнительная информация

Все внутренние изменения полностью обратно совместимы на уровне API. Если у вас возникли проблемы с миграцией, создайте issue в репозитории проекта.
