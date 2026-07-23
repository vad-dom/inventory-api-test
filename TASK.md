# Тестовое задание для Middle PHP Laravel Developer

## Тема

Разработать REST API backend-сервера для складского учета товаров и остатков.

Оценочное время выполнения: **5 часов**.

Задание проверяет:

- проектирование REST API;
- работу с Laravel;
- миграции и связи БД;
- валидацию;
- сервисный слой;
- репозитории;
- транзакции;
- единый формат JSON-ответов;
- обработку бизнес-ошибок;
- базовую OpenAPI-документацию;
- feature-тесты.

---

## Ограничения по архитектуре

Использовать только базовый функционал Laravel.

Разрешено:

- Controllers
- Form Requests
- API Resources
- Models
- Services
- Repositories
- Middleware
- стандартный Exception Handler Laravel
- Laravel Cache facade
- Laravel Database Transactions
- PHP enums или enum-like константы в моделях

Запрещено:

- DTO
- Action-классы
- Use Case-классы
- CQRS
- Event Sourcing
- GraphQL
- API Platform
- кастомные фреймворки поверх Laravel
- внешние пакеты для бизнес-логики
- отдельный слой Entity вне Eloquent
- абстрактные интерфейсы ради интерфейсов
- избыточная архитектура без практической необходимости

---

## Технологический стек

Обязательно:

- PHP 8.2+
- Laravel 10+
- MariaDB или MySQL
- Eloquent ORM
- Laravel Migrations
- Laravel Form Request Validation
- Laravel API Resources
- PHPUnit или Pest
- OpenAPI 3.0 / Swagger-документация в файле `docs/openapi.yaml`

Допускается:

- Redis через стандартный Laravel Cache facade
- Docker / Docker Compose
- Laravel Sail

---

## Предметная область

Нужно реализовать API для учета товаров на складах.

Система должна позволять:

- создавать товары;
- создавать склады;
- просматривать остатки;
- выполнять приход товара;
- выполнять списание товара;
- выполнять перемещение товара между складами;
- хранить историю складских операций;
- получать краткую статистику по остаткам.

---

## Сущности

### Product

Товар.

Поля:

- `id`
- `sku`
- `name`
- `description`
- `is_active`
- `created_at`
- `updated_at`

Ограничения:

- `sku` — обязательное, строка, максимум 64 символа, уникальное значение
- `name` — обязательное, строка, максимум 255 символов
- `description` — необязательное, строка, максимум 2000 символов
- `is_active` — boolean, по умолчанию `true`

---

### Warehouse

Склад.

Поля:

- `id`
- `name`
- `code`
- `is_active`
- `created_at`
- `updated_at`

Ограничения:

- `name` — обязательное, строка, максимум 255 символов
- `code` — обязательное, строка, максимум 64 символа, уникальное значение
- `is_active` — boolean, по умолчанию `true`

---

### StockBalance

Остаток товара на конкретном складе.

Поля:

- `id`
- `product_id`
- `warehouse_id`
- `quantity`
- `created_at`
- `updated_at`

Ограничения:

- `product_id` — обязательное, существующий товар
- `warehouse_id` — обязательное, существующий склад
- `quantity` — целое число, не меньше 0
- пара `product_id + warehouse_id` должна быть уникальной

---

### StockMovement

История складских операций.

Поля:

- `id`
- `product_id`
- `source_warehouse_id`
- `target_warehouse_id`
- `type`
- `quantity`
- `comment`
- `created_at`
- `updated_at`

Типы операций:

- `income` — приход товара на склад
- `write_off` — списание товара со склада
- `transfer` — перемещение между складами

Ограничения:

- `product_id` — обязательное, существующий товар
- `source_warehouse_id` — обязательное для `write_off` и `transfer`
- `target_warehouse_id` — обязательное для `income` и `transfer`
- `type` — обязательное, одно из допустимых значений
- `quantity` — обязательное, целое число, минимум 1
- `comment` — необязательное, строка, максимум 2000 символов

---

## Базовый URL

```text
/api/v1
```

---

## Авторизация

Упрощенная авторизация через API-ключ.

Все запросы должны содержать заголовок:

```http
X-Api-Key: <ключ>
```

Ключ хранится в `.env`:

```env
API_KEY=local-test-key
```

Если ключ отсутствует или неверный, API должно вернуть HTTP-код `401` в едином JSON-формате.

Реализовать через стандартный Laravel middleware.

---

## Единый формат ответов

Все ответы API должны быть JSON.

Структура ответа должна быть одинаковой для всех контроллеров.

### Успешный ответ с объектом

```json
{
  "success": true,
  "data": {
    "id": 1,
    "sku": "CPU-001",
    "name": "Intel Core i5"
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Успешный ответ со списком

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sku": "CPU-001",
      "name": "Intel Core i5"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 15,
    "total": 1,
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Ошибка валидации

HTTP-код: `422`

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed.",
    "fields": {
      "sku": [
        "The sku field is required."
      ]
    }
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Ошибка авторизации

HTTP-код: `401`

```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Invalid API key."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Ресурс не найден

HTTP-код: `404`

```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Resource not found."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Конфликт бизнес-правил

HTTP-код: `409`

```json
{
  "success": false,
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Not enough stock for this operation."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Внутренняя ошибка

HTTP-код: `500`

```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "Internal server error."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

---

## Endpoints

## Products

### Создать товар

```http
POST /api/v1/products
```

Body:

```json
{
  "sku": "CPU-001",
  "name": "Intel Core i5",
  "description": "Desktop processor",
  "is_active": true
}
```

### Получить список товаров

```http
GET /api/v1/products
```

Query-параметры:

- `page`
- `per_page`
- `search`
- `is_active`
- `sort`
- `direction`

Фильтры:

- `search` — поиск по `sku`, `name`, `description`
- `is_active` — фильтр по активности

Сортировка:

- `sort=id|sku|name|created_at|updated_at`
- `direction=asc|desc`

Значения по умолчанию:

- `sort=created_at`
- `direction=desc`
- `per_page=15`

`per_page` не должен быть больше 100.

### Получить товар

```http
GET /api/v1/products/{id}
```

Ответ должен включать:

- данные товара;
- суммарный остаток по всем складам;
- остатки по складам.

### Обновить товар

```http
PUT /api/v1/products/{id}
```

Body:

```json
{
  "sku": "CPU-001",
  "name": "Intel Core i5-12400",
  "description": "Desktop processor",
  "is_active": true
}
```

### Деактивировать товар

```http
PATCH /api/v1/products/{id}/deactivate
```

Товар нельзя деактивировать, если по нему есть положительный остаток хотя бы на одном складе.

Если остаток есть, вернуть `409`.

```json
{
  "success": false,
  "error": {
    "code": "PRODUCT_HAS_STOCK",
    "message": "Product has stock and cannot be deactivated."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Удалить товар

```http
DELETE /api/v1/products/{id}
```

Удаление товара запрещено, если:

- по товару есть положительный остаток;
- по товару есть история складских операций.

В этих случаях вернуть `409`.

```json
{
  "success": false,
  "error": {
    "code": "PRODUCT_CANNOT_BE_DELETED",
    "message": "Product has stock or movements and cannot be deleted."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

---

## Warehouses

### Создать склад

```http
POST /api/v1/warehouses
```

Body:

```json
{
  "name": "Main warehouse",
  "code": "MAIN",
  "is_active": true
}
```

### Получить список складов

```http
GET /api/v1/warehouses
```

Query-параметры:

- `page`
- `per_page`
- `search`
- `is_active`

`search` должен искать по `name` и `code`.

### Получить склад

```http
GET /api/v1/warehouses/{id}
```

Ответ должен включать:

- данные склада;
- количество товарных позиций с положительным остатком;
- суммарное количество единиц товара на складе.

### Обновить склад

```http
PUT /api/v1/warehouses/{id}
```

Body:

```json
{
  "name": "Main warehouse updated",
  "code": "MAIN",
  "is_active": true
}
```

### Деактивировать склад

```http
PATCH /api/v1/warehouses/{id}/deactivate
```

Склад нельзя деактивировать, если на нем есть положительный остаток.

Если остаток есть, вернуть `409`.

```json
{
  "success": false,
  "error": {
    "code": "WAREHOUSE_HAS_STOCK",
    "message": "Warehouse has stock and cannot be deactivated."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Удалить склад

```http
DELETE /api/v1/warehouses/{id}
```

Удаление склада запрещено, если:

- на складе есть положительный остаток;
- склад используется в истории складских операций.

В этих случаях вернуть `409`.

```json
{
  "success": false,
  "error": {
    "code": "WAREHOUSE_CANNOT_BE_DELETED",
    "message": "Warehouse has stock or movements and cannot be deleted."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

---

## Stock operations

Складские операции должны выполняться в транзакциях.

После каждой успешной операции должна создаваться запись в `stock_movements`.

Остатки не должны уходить в минус.

### Приход товара

```http
POST /api/v1/stock/income
```

Body:

```json
{
  "product_id": 1,
  "warehouse_id": 1,
  "quantity": 10,
  "comment": "Initial stock"
}
```

Логика:

- проверить, что товар существует и активен;
- проверить, что склад существует и активен;
- увеличить остаток товара на складе;
- создать запись движения типа `income`.

### Списание товара

```http
POST /api/v1/stock/write-off
```

Body:

```json
{
  "product_id": 1,
  "warehouse_id": 1,
  "quantity": 3,
  "comment": "Damaged items"
}
```

Логика:

- проверить, что товар существует и активен;
- проверить, что склад существует и активен;
- проверить, что остатка достаточно;
- уменьшить остаток товара на складе;
- создать запись движения типа `write_off`.

Если товара недостаточно, вернуть `409`.

```json
{
  "success": false,
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Not enough stock for this operation."
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Перемещение товара между складами

```http
POST /api/v1/stock/transfer
```

Body:

```json
{
  "product_id": 1,
  "source_warehouse_id": 1,
  "target_warehouse_id": 2,
  "quantity": 5,
  "comment": "Transfer to secondary warehouse"
}
```

Логика:

- проверить, что товар существует и активен;
- проверить, что оба склада существуют и активны;
- проверить, что исходный и целевой склад отличаются;
- проверить, что остатка на исходном складе достаточно;
- уменьшить остаток на исходном складе;
- увеличить остаток на целевом складе;
- создать запись движения типа `transfer`.

Если исходный и целевой склад совпадают, вернуть `422`.

---

## Stock balances

### Получить список остатков

```http
GET /api/v1/stock/balances
```

Query-параметры:

- `page`
- `per_page`
- `product_id`
- `warehouse_id`
- `only_positive`
- `sort`
- `direction`

Фильтры:

- `product_id` — фильтр по товару
- `warehouse_id` — фильтр по складу
- `only_positive=true` — показывать только остатки больше 0

Сортировка:

- `sort=quantity|created_at|updated_at`
- `direction=asc|desc`

Ответ должен включать товар и склад.

### Получить остаток товара на складе

```http
GET /api/v1/stock/balances/{product}/{warehouse}
```

Пример ответа:

```json
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "sku": "CPU-001",
      "name": "Intel Core i5"
    },
    "warehouse": {
      "id": 1,
      "code": "MAIN",
      "name": "Main warehouse"
    },
    "quantity": 10
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

Если записи остатка нет, вернуть количество `0`, а не `404`.

---

## Stock movements

### Получить историю складских операций

```http
GET /api/v1/stock/movements
```

Query-параметры:

- `page`
- `per_page`
- `product_id`
- `warehouse_id`
- `type`
- `created_from`
- `created_to`
- `sort`
- `direction`

Фильтры:

- `product_id` — фильтр по товару
- `warehouse_id` — поиск по `source_warehouse_id` или `target_warehouse_id`
- `type` — `income|write_off|transfer`
- `created_from` — дата начала периода
- `created_to` — дата конца периода

Сортировка:

- `sort=created_at|quantity|type`
- `direction=asc|desc`

Ответ должен включать:

- товар;
- склад-источник, если есть;
- склад-получатель, если есть.

---

## Statistics

### Получить статистику по складам

```http
GET /api/v1/statistics/stock
```

Пример ответа:

```json
{
  "success": true,
  "data": {
    "products_total": 25,
    "warehouses_total": 3,
    "positions_with_stock": 14,
    "total_quantity": 350,
    "by_warehouse": [
      {
        "warehouse_id": 1,
        "warehouse_code": "MAIN",
        "warehouse_name": "Main warehouse",
        "positions": 8,
        "quantity": 220
      }
    ],
    "low_stock": [
      {
        "product_id": 1,
        "sku": "CPU-001",
        "name": "Intel Core i5",
        "total_quantity": 2
      }
    ]
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

Правила:

- `low_stock` — товары, у которых суммарный остаток по всем складам меньше 5, но больше 0;
- статистику можно кэшировать на 60 секунд;
- кэш должен инвалидироваться после любой складской операции, создания, обновления, удаления или деактивации товара/склада.

---

## Требования к БД

Нужно создать миграции:

- `products`
- `warehouses`
- `stock_balances`
- `stock_movements`

### Индексы

Обязательные индексы:

- `products.sku` — unique
- `warehouses.code` — unique
- `stock_balances.product_id`
- `stock_balances.warehouse_id`
- `stock_balances.product_id + warehouse_id` — unique
- `stock_movements.product_id`
- `stock_movements.source_warehouse_id`
- `stock_movements.target_warehouse_id`
- `stock_movements.type`
- `stock_movements.created_at`

### Не внешние ключи

Связи:

- `stock_balances.product_id` → `products.id`
- `stock_balances.warehouse_id` → `warehouses.id`
- `stock_movements.product_id` → `products.id`
- `stock_movements.source_warehouse_id` → `warehouses.id`
- `stock_movements.target_warehouse_id` → `warehouses.id`

Удаление:

- физическое удаление товара или склада не производится - только отметка deleted_at;
- каскадное удаление истории операций не использовать.

---

## Валидация

Вся валидация должна быть реализована через Form Request.

Обязательные проверки:

- уникальность `products.sku`;
- уникальность `warehouses.code`;
- существование `product_id`;
- существование `warehouse_id`;
- существование `source_warehouse_id`;
- существование `target_warehouse_id`;
- допустимое значение `type`;
- `quantity` — integer, минимум 1;
- `is_active` — boolean;
- `per_page` — integer, минимум 1, максимум 100;
- whitelist для `sort`;
- whitelist для `direction`;
- `created_from` и `created_to` — валидные даты;
- `created_from` не должен быть позже `created_to`;
- при перемещении исходный склад не должен совпадать с целевым.

---

## Бизнес-правила

### Товар

- Операции с остатками разрешены только для активных товаров.
- Товар нельзя деактивировать, если по нему есть положительный остаток.
- Товар нельзя удалить, если по нему есть остатки или история операций.

### Склад

- Операции с остатками разрешены только для активных складов.
- Склад нельзя деактивировать, если на нем есть положительный остаток.
- Склад нельзя удалить, если по нему есть остатки или история операций.

### Остатки

- Остаток не может быть отрицательным.
- Приход увеличивает остаток.
- Списание уменьшает остаток.
- Перемещение уменьшает остаток на складе-источнике и увеличивает на складе-получателе.
- Любое изменение остатков должно выполняться внутри транзакции.
- При одновременных операциях не должно возникать отрицательных остатков.

---

## Рекомендуемая структура проекта

```text
app/
  Http/
    Controllers/
      Api/
        V1/
          Controllers
    Middleware/
      ApiKeyMiddleware.php
    Requests/
      Product/
        Requests
      Warehouse/
        Requests
      Stock/
        Requests
    Resources/
      Resources
  Models/
    Models
  Repositories/
    Repositories
  Services/
    Services
```

---

## Роли слоев

### Controller

Контроллер должен:

- принимать HTTP-запрос;
- использовать Form Request;
- вызывать сервис;
- возвращать API Resource или единый JSON-ответ;
- не содержать бизнес-логику;
- не выполнять прямые SQL-запросы.

### Service

Сервис должен:

- содержать бизнес-логику;
- проверять доступность товара и склада;
- проверять достаточность остатка;
- выполнять складские операции в транзакциях;
- создавать записи истории операций;
- инвалидировать кэш статистики;
- вызывать репозитории.

### Repository

Репозиторий должен:

- инкапсулировать запросы к БД;
- содержать фильтрацию;
- содержать сортировку;
- содержать пагинацию;
- не работать напрямую с HTTP Request;
- не содержать бизнес-логику.

### Model

Модель должна содержать:

- `fillable`;
- `casts`;
- Eloquent relationships;
- enum-like константы или PHP enum для типов движений.

---

## Ошибки

Минимальный список ошибок:

| HTTP-код | Код ошибки | Ситуация |
|---:|---|---|
| 400 | `BAD_REQUEST` | Некорректный запрос |
| 401 | `UNAUTHORIZED` | Нет API-ключа или он неверный |
| 404 | `NOT_FOUND` | Ресурс не найден |
| 409 | `INSUFFICIENT_STOCK` | Недостаточно товара для операции |
| 409 | `PRODUCT_HAS_STOCK` | Товар нельзя деактивировать из-за остатка |
| 409 | `WAREHOUSE_HAS_STOCK` | Склад нельзя деактивировать из-за остатка |
| 409 | `PRODUCT_CANNOT_BE_DELETED` | Товар нельзя удалить |
| 409 | `WAREHOUSE_CANNOT_BE_DELETED` | Склад нельзя удалить |
| 422 | `VALIDATION_ERROR` | Ошибка валидации |
| 500 | `INTERNAL_ERROR` | Внутренняя ошибка сервера |

---

## Документация API

Нужно предоставить файл:

```text
docs/openapi.yaml
```

Минимальные требования:

- описание всех endpoints;
- описание query-параметров;
- описание request body;
- описание response body;
- описание ошибок;
- описание заголовка `X-Api-Key`;
- примеры успешных и ошибочных ответов.

Swagger UI поднимать необязательно.

---

## Тесты

Минимум 10 feature-тестов:

1. создание товара;
2. ошибка валидации при создании товара без `sku`;
3. создание склада;
4. приход товара увеличивает остаток;
5. списание товара уменьшает остаток;
6. списание больше доступного остатка возвращает `409`;
7. перемещение товара между складами изменяет оба остатка;
8. перемещение товара на тот же склад возвращает `422`;
9. товар с положительным остатком нельзя деактивировать;
10. получение списка остатков с фильтром `only_positive=true`.

Дополнительно приветствуется:

- тест API-ключа;
- тест запрета удаления товара с историей операций;
- тест запрета удаления склада с историей операций;
- тест списка движений с фильтрацией по типу;
- тест статистики;
- тест инвалидации кэша статистики.

---

## Seeder

Нужно добавить сидер с тестовыми данными:

- минимум 10 товаров;
- минимум 3 склада;
- минимум 20 остатков;
- минимум 30 складских операций.

Запуск:

```bash
php artisan migrate --seed
```

---

## README

В корне проекта должен быть `README.md`.

Минимальное содержание:

- версия PHP;
- версия Laravel;
- инструкция запуска;
- переменные `.env`;
- запуск миграций;
- запуск сидеров;
- запуск тестов;
- путь к OpenAPI-документации;
- краткое описание архитектуры;
- список принятых технических решений;
- описание бизнес-правил по складским операциям.

---

## Требования к Git

Результат должен быть передан ссылкой на Git-репозиторий или архивом проекта.

В репозитории не должно быть:

- `.env`;
- `vendor`;
- `node_modules`;
- дампов БД;
- временных файлов IDE.

Должен быть файл:

```text
.env.example
```

---

## Проверочный сценарий

После получения проекта проверяющий должен иметь возможность выполнить:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan test
php artisan serve
```

После запуска API должно быть доступно по адресу:

```text
http://127.0.0.1:8000/api/v1
```

Пример запроса:

```bash
curl -H "X-Api-Key: local-test-key" \
  "http://127.0.0.1:8000/api/v1/stock/balances?only_positive=true&per_page=10"
```

---

## Критерии оценки

### API и REST

Оценивается:

- корректные HTTP-методы;
- корректные HTTP-коды;
- единый формат JSON-ответов;
- корректная пагинация;
- корректная фильтрация;
- корректная сортировка;
- отсутствие HTML-ответов в API;
- предсказуемые ошибки.

### Laravel

Оценивается:

- использование стандартных механизмов Laravel;
- Form Request вместо ручной валидации в контроллерах;
- API Resources для сериализации;
- Eloquent relationships;
- миграции;
- сидеры;
- feature-тесты;
- middleware для API-ключа;
- централизованная обработка ошибок;
- транзакции через `DB::transaction`.

### Архитектура

Оценивается:

- понятное разделение Controller / Service / Repository;
- отсутствие бизнес-логики в контроллерах;
- отсутствие SQL-запросов в контроллерах;
- отсутствие DTO, Actions и аналогичных дополнительных слоев;
- отсутствие избыточных абстракций;
- читаемость структуры проекта.

### Бизнес-логика

Оценивается:

- корректный приход товара;
- корректное списание товара;
- корректное перемещение товара;
- запрет отрицательных остатков;
- запрет операций с неактивными товарами и складами;
- запрет деактивации товара с остатком;
- запрет деактивации склада с остатком;
- корректная история движений;
- корректная статистика;
- инвалидация кэша статистики.

### Качество кода

Оценивается:

- строгие типы там, где это уместно;
- читаемые имена классов и методов;
- небольшие методы;
- отсутствие дублирования;
- отсутствие мертвого кода;
- отсутствие скрытых side effects;
- корректная обработка исключений;
- отсутствие прямой зависимости бизнес-логики от HTTP Request.

---

## Что не требуется

Не требуется:

- frontend;
- админ-панель;
- полноценная регистрация пользователей;
- роли и права доступа;
- очереди;
- WebSocket;
- загрузка файлов;
- бухгалтерский учет;
- учет цен и себестоимости;
- интеграция с внешними системами;
- микросервисная архитектура.

---

## Дополнительные задания, если осталось время

Эти пункты необязательны.

### Healthcheck

```http
GET /api/v1/health
```

Ответ:

```json
{
  "success": true,
  "data": {
    "status": "ok"
  },
  "meta": {
    "request_id": "01JTESTREQUESTID"
  }
}
```

### Фильтр низких остатков

Добавить endpoint:

```http
GET /api/v1/stock/low
```

Query-параметры:

- `threshold`

Если `threshold` не передан, использовать `5`.

### Экспорт истории операций

Добавить endpoint:

```http
GET /api/v1/stock/movements/export
```

Формат:

- CSV;
- фильтры такие же, как у `/api/v1/stock/movements`.

Этот пункт необязательный и не должен ломать единый JSON-формат основных API-ответов.
