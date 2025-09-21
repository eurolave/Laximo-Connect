# Laximo PHP Microservice — GitHub-Ready (PHP 8.2, Railway)

Готовая структура для заливки в GitHub и деплоя на Railway.

## Что уже сделано
- Обновлён `composer.json`: `"php": "^8.2"`, сохранены `ext-soap`, `ext-simplexml`, `laximo/guayaquillib`.
- Старт использует порт из `$PORT` (Railway): `php -S 0.0.0.0:$PORT -t public public/index.php`.
- Добавлен `Procfile` (на случай автодетекта).
- Добавлен минимальный роутер `public/index.php` (health-check и `/api`). Замените на свою логику.

## Локальный запуск
```bash
composer install
composer run start
# или вручную:
# PORT=8080 php -S 0.0.0.0:$PORT -t public public/index.php
```

## Заливка в GitHub
```bash
git init
git add .
git commit -m "Initial: PHP 8.2 + Railway ready"
git branch -M main
git remote add origin <URL_репозитория>
git push -u origin main
```

## Деплой на Railway
1. Railway → **New Project → Deploy from GitHub Repo** → выбрать репозиторий.
2. Во вкладке **Variables** добавьте секреты (если нужны).
3. Нажмите **Redeploy** (если добавляли переменные после билда).
4. Проверяйте **Build/Runtime Logs**.

> Если у вас уже есть собственная точка входа, поправьте команду в `composer.json`/`Procfile`.
