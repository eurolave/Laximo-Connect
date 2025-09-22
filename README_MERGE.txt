MERGE INSTRUCTIONS (для существующего репозитория)

Добавляем в проект поддержку laximo/guayaquillib через обёртку + эндпоинты.

Состав архива:
- src/GuayaquilClient.php
- public/index.php

Как внедрить:
1) Скопируйте файлы из этого архива в ваш репозиторий, соблюдая пути (src/, public/).
   - Если у вас уже есть public/index.php, сравните и объедините вручную маршруты (/catalogs, /catalog/:code).
2) Убедитесь, что в composer.json есть зависимости:
   {
     "require": {
       "php": "^8.2",
       "ext-soap": "*",
       "ext-simplexml": "*",
       "laximo/guayaquillib": "^3.0"
     },
     "autoload": {
       "psr-4": {
         "App\\": "src/"
       }
     },
     "scripts": {
       "start": "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"
     }
   }
3) Выполните:
   composer install
   composer dump-autoload
4) Локально проверьте:
   PORT=8080 php -S 0.0.0.0:$PORT -t public public/index.php
   - http://localhost:8080/catalogs
   - http://localhost:8080/catalog/CFIAT84 (пример)
5) На Railway добавьте переменные окружения:
   - LAXIMO_LOGIN
   - LAXIMO_PASSWORD
6) Задеплойте. Примеры URL:
   - https://<ваш-домен>.up.railway.app/catalogs
   - https://<ваш-домен>.up.railway.app/catalog/CFIAT84

Примечание:
- Если вам нужны другие методы SDK (AM/DOC и т.д.), добавьте их в App\GuayaquilClient и создайте маршруты в public/index.php.

