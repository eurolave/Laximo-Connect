# Laximo PHP Microservice (GuayaquilLib)

Мини-сервис на PHP поверх **laximo/guayaquillib** (CAT/DOC), для вызова из Node/Telegram бота.

## Эндпоинты
- `GET /health`
- `GET /cat/findVehicle?vin=WAUZZZ...` → `{ vehicleid, ssd, catalog, brand, name, raw }`
- `GET /cat/listUnits?catalog=TOYOTA00&ssd=$...&category=0&group=1` → `{ assemblies: [...], raw }`
- `GET /cat/listDetailByUnit?catalog=TOYOTA00&ssd=$...&unitid=3423` → `{ items: [...], raw }`
- `GET /doc/partByOem?oem=C110&brand=VIC` → `{ oem, brand, name, raw }`
- `GET /doc/crosses?oem=C110` → `{ crosses: [...], raw }`

## Переменные окружения
- `LAXIMO_LOGIN`, `LAXIMO_PASSWORD` — учётные данные Laximo

## Локальный запуск
```bash
composer install
php -S 0.0.0.0:8080 -t public
# http://localhost:8080/health
```

## Railway
- Создайте новый проект и загрузите ZIP.
- **Start Command:** `php -S 0.0.0.0:$PORT -t public`
- Добавьте переменные в **Variables**: `LAXIMO_LOGIN`, `LAXIMO_PASSWORD`.
- После деплоя — `https://<app>.up.railway.app/health`.

## Подключение Node-бота
В Node поставьте:
```
LAXIMO_BASE_URL=https://<ваш-php-сервис>.up.railway.app
LAXIMO_PATH_FINDVEHICLE=/cat/findVehicle
LAXIMO_PATH_LIST_UNITS=/cat/listUnits
LAXIMO_PATH_LIST_PARTS=/cat/listDetailByUnit
LAXIMO_PATH_PART_BY_OEM=/doc/partByOem
LAXIMO_PATH_CROSSES_BY_OEM=/doc/crosses
LAXIMO_DEFAULT_CATEGORY=0
LAXIMO_DEFAULT_GROUP=1
```
