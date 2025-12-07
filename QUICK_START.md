# 🚀 QUICK START GUIDE

## Требования
- Docker Desktop (запущенный)
- PHP 8.2+ (для локальной разработки)
- Node.js 18+ (для frontend)
- Composer
- npm/yarn

## 1. Запуск Backend (Docker)

### 1.1 Запуск Docker Desktop
```bash
# Запустить Docker Desktop из меню Пуск
# Дождаться полного запуска (значок в трее станет зеленым)
```

### 1.2 Запуск контейнеров
```bash
cd brawl-leaderboard
docker-compose up -d
```

### 1.3 Применение миграций БД
```bash
# Проверить статус контейнеров
docker-compose ps

# Применить миграции по порядку
docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations/001_create_users_table.sql
docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations/002_create_players_table.sql
docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations/003_create_seasons_table.sql
docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations/004_create_scores_table.sql
docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations/005_create_score_history_table.sql

# Проверить созданные таблицы
docker exec brawl-postgres psql -U postgres -d brawl_stars -c "\dt"
```

### 1.4 Проверка API
```bash
# Health check
curl http://localhost/api/health

# Должен вернуть JSON со статусом сервисов
```

## 2. Запуск Frontend

### 2.1 Установка зависимостей
```bash
cd leaderboard-frontend
npm install
```

### 2.2 Создание .env файла
```bash
cp .env.example .env
# Отредактировать .env при необходимости
```

### 2.3 Запуск dev сервера
```bash
npm run dev
# Frontend будет доступен на http://localhost:5173
```

## 3. Альтернативный запуск (без Docker Desktop)

Если Docker Desktop недоступен, можно запустить сервисы по отдельности:

### 3.1 PostgreSQL локально
```bash
# Установить PostgreSQL 15+
# Создать базу данных "brawl_stars"
# Применить миграции через psql:
psql -U postgres -d brawl_stars -f migrations/001_create_users_table.sql
# ... и так далее для всех миграций
```

### 3.2 Redis локально
```bash
# Установить Redis
# Запустить: redis-server
```

### 3.3 PHP локально
```bash
cd brawl-leaderboard
composer install
php -S localhost:8000 -t public
```

## 4. Проверка работоспособности

### 4.1 Backend API
```bash
# Health endpoint
curl http://localhost/api/health

# Регистрация пользователя
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","nickname":"TestPlayer"}'

# Получение лидерборда
curl http://localhost/api/leaderboards/global
```

### 4.2 Frontend
- Открыть http://localhost:5173
- Проверить загрузку страниц
- Попробовать зарегистрироваться

## 5. Полезные команды

### Docker
```bash
# Просмотр логов
docker-compose logs -f

# Перезапуск контейнеров
docker-compose restart

# Остановка контейнеров
docker-compose down

# Полная очистка (внимание: удалит данные!)
docker-compose down -v
```

### Backend
```bash
# Проверка кода
composer cs-check
composer phpstan

# Запуск тестов
composer test
```

### Frontend
```bash
# Сборка для production
npm run build

# Запуск тестов
npm test
```

## 6. Структура проекта

```
brawl-leaderboard/
├── src/                    # Backend PHP код
│   ├── Domain/            # Бизнес-логика
│   ├── Application/       # Use cases и сервисы
│   ├── Infrastructure/    # Контроллеры, репозитории
│   └── Framework/        # Роутер, DI контейнер
├── migrations/            # Миграции БД
├── public/               # Entry point (index.php)
├── docker/               # Docker конфиги
├── leaderboard-frontend/ # React TypeScript приложение
└── docs/                # Документация
```

## 7. Следующие шаги разработки

1. ✅ Запустить Docker и применить миграции
2. 🔄 Проверить работу API endpoints
3. 🔄 Создать frontend компоненты
4. 🔄 Настроить аутентификацию
5. 🔄 Написать тесты
6. 🔄 Создать документацию

## 8. Troubleshooting

### Docker не запускается
- Проверить, запущен ли Docker Desktop
- Проверить доступность портов 80, 5432, 6379
- Перезапустить Docker Desktop

### База данных не подключается
- Проверить логи: `docker-compose logs postgres`
- Убедиться, что миграции применены
- Проверить переменные окружения в .env

### Frontend не подключается к API
- Проверить CORS настройки в nginx.conf
- Убедиться, что backend запущен на порту 80
- Проверить .env файл в frontend

### PHP ошибки
- Проверить логи: `docker-compose logs php-fpm`
- Убедиться, что зависимости установлены: `composer install`
- Проверить права доступа к файлам