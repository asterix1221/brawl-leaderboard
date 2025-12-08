# FAQ

## Как запустить проект локально?
1. Создайте `.env` в корне бэкенда по образцу из README.
2. Запустите инфраструктуру: `docker-compose up -d`.
3. Примените миграции: `./migrate.sh` (или `migrate.bat` на Windows).
4. Соберите фронтенд: `cd leaderboard-frontend && npm install && npm run dev`.

## Где доступен API?
- Бэкенд слушает на `http://localhost` c API-префиксом `/api` (пример: `/api/health`).
- За проксирование отвечает контейнер `nginx` из `docker-compose.yml` или `docker-compose.prod.yml`.

## Как собирать и запускать прод окружение?
1. Соберите фронтенд: `cd leaderboard-frontend && npm ci && npm run build`.
2. Убедитесь, что `.env` содержит production-настройки (секреты, `APP_ENV=production`, `APP_DEBUG=false`).
3. Запустите: `docker-compose -f docker-compose.prod.yml up -d --build`.

## Как применить миграции в контейнерах?
- Для локальной разработки: `./migrate.sh` выполняет SQL из каталога `migrations`.
- В контейнере PHP-FPM можно выполнить:
  ```bash
  docker-compose exec php-fpm php src/Framework/Database/migrate.php
  ```

## Что делать при ошибке прав доступа к файлам?
- На Unix-системах можно выдать права пользователю www-data на проектные каталоги:
  ```bash
  sudo chown -R $USER:www-data src public vendor
  ```
- В Docker томах примените `docker-compose exec php-fpm chown -R www-data:www-data /var/www/html`.

## Как обновлять зависимости?
- Backend: `composer update` (в dev) или `COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev` (в prod).
- Frontend: `npm update` или установка конкретных пакетов `npm install <package>@latest`.

## Как проверить работоспособность?
- API: `curl http://localhost/api/health`.
- Бэкенд контейнеры: `docker-compose ps` и `docker-compose logs`.
- Фронтенд: страница dev-сервера доступна по `http://localhost:5173` после `npm run dev`.
