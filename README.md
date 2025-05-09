# Maxmoll Test

Этот проект представляет собой REST API-систему для управления заказами, товарами, складами и историей движения
остатков.

---

## Установка

1. Клонируйте репозиторий:
   ```bash
   https://github.com/Arman-211/maxmoll-test.git
   cd maxmoll-test
   ```

2. Установите зависимости:

   ```bash
    composer install
   ```

3. Создайте файл .env:

   ```bash
    cp .env.example .env
    php artisan key:generate
   ```

Настройте параметры подключения к базе данных в файле .env.

4. Выполните миграции и сидеры:

   ```bash
    php artisan migrate --seed
   ```

Или выполнить сидер отдельно

  ```bash
    php artisan db:seed --class=ReferenceSeeder
  ```

## API

Все маршруты находятся под префиксом `/api/test`.

### Доступные эндпоинты

- `GET    /api/test/warehouses` — список складов
- `GET    /api/test/products` — список товаров с остатками
- `GET    /api/test/orders` — список заказов
- `POST   /api/test/orders` — создание заказа
- `PUT    /api/test/orders/{id}` — редактирование заказа
- `POST   /api/test/orders/{id}/{status}` — смена статуса заказа (`complete`, `cancel`, `resume`)
- `GET    /api/test/stock-movements` — история движения остатков

### Поддержка фильтров и пагинации

Методы `orders` и `stock-movements` поддерживают следующие GET-параметры:

- `product_id` — фильтрация по товару
- `warehouse_id` — фильтрация по складу
- `date_from` — фильтрация от даты (формат YYYY-MM-DD)
- `date_to` — фильтрация до даты (формат YYYY-MM-DD)
- `per_page` — количество записей на страницу (по умолчанию 10)

### Пример запроса с фильтрами

```http
GET /api/test/stock-movements?product_id=1&warehouse_id=2&date_from=2025-05-01&date_to=2025-05-10&per_page=20
```
## Postman

В корне проекта находится файл [`MaxmollTest.postman_collection.json`](./MaxmollTest.postman_collection.json), который можно импортировать в Postman для быстрого тестирования всех API-эндпоинтов.

### Как использовать:

1. Откройте Postman.
2. Нажмите кнопку **Import**.
3. Перейдите на вкладку **File**.
4. Выберите файл `Maxmoll.postman_collection.json` из корня проекта.
5. После импорта выберите коллекцию **Maxmoll Test** и начните выполнять запросы.

Коллекция содержит все маршруты из префикса `/api/test`, включая:
- создание заказов
- изменение статусов
- фильтрацию по остаткам
- просмотр складов и товаров
