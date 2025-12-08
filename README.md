# 🏆 Brawl Stars Leaderboard

Полнофункциональная система лидербордов для игры Brawl Stars с Clean Architecture на PHP 8.2 и современным React TypeScript frontend.

## 🚀 Quick Start

### Требования
- Docker Desktop (запущенный)
- Node.js 18+
- PHP 8.2+ (для локальной разработки)
- Composer

### 1. Запуск Backend

```bash
cd brawl-leaderboard

# Установить зависимости Backend (выберите вариант)
composer install
# или собрать образы с установкой зависимостей внутри
docker compose build --build-arg COMPOSER_AUTH="${COMPOSER_AUTH:-}" --build-arg COMPOSER_TOKEN="${COMPOSER_TOKEN:-}"

# Запустить Docker контейнеры
docker-compose up -d

# Применить миграции БД
.\migrate.bat

# Проверить API
curl http://localhost/api/health
```

### 2. Запуск Frontend

```bash
# Установить зависимости
cd leaderboard-frontend
npm install

# Создать .env файл
cp .env.example .env

# Проверить, что VITE_API_URL в .env указывает на актуальный backend (по умолчанию http://localhost/api)

# Запустить dev сервер с доступом извне контейнера/WSL и фиксированным портом
npm run dev -- --host --port 5173
# или используйте добавленный скрипт
npm run dev:host
```

Приложение будет доступно на:
- 🌐 Frontend: http://localhost:5173 (если порт свободен)
- 🔌 API: http://localhost/api

> ⚠️ Без флага `--host` Vite слушает только localhost процесса, поэтому из браузера в хост-системе (Windows/WSL, Docker Desktop) страница будет недоступна.
>
> 💡 Если порт 5173 занят, Vite запустится на другом порту. Посмотрите адрес в выводе команды или задайте конкретный порт через `--port 5173` (при необходимости добавьте `--strictPort`, чтобы получить ошибку, если порт занят).
>
> 🔒 Для доступа к странице из браузера на Windows/WSL может потребоваться проброс порта (Docker Desktop / wsl --user) и разрешение трафика в брандмауэре Windows.

## 📁 Структура проекта

```
brawl-leaderboard/
├── 📁 src/                          # Backend PHP код
│   ├── Domain/                       # Бизнес-логика (Entities, Value Objects)
│   ├── Application/                   # Use Cases, DTO, Services
│   ├── Infrastructure/                # Controllers, Repositories, Middleware
│   └── Framework/                    # Router, DI Container, Database
├── 📁 migrations/                    # Миграции PostgreSQL
├── 📁 docker/                        # Docker конфигурации
├── 📁 public/                       # Entry point (index.php)
├── 📁 leaderboard-frontend/          # React TypeScript приложение
│   ├── src/
│   │   ├── components/               # React компоненты
│   │   ├── pages/                   # Страницы приложения
│   │   ├── services/                # API клиенты
│   │   ├── store/                   # Zustand state management
│   │   ├── types/                   # TypeScript типы
│   │   └── utils/                   # Утилиты
│   └── package.json
├── 📄 docker-compose.yml             # Docker сервисы
├── 📄 composer.json                 # PHP зависимости
└── 📄 README.md                     # Этот файл
```

## 🏗️ Архитектура

### Backend (Clean Architecture)
- **Domain Layer**: Бизнес-логика, Entities, Value Objects
- **Application Layer**: Use Cases, DTO, Services  
- **Infrastructure Layer**: Controllers, Repositories, External APIs
- **Framework Layer**: Router, DI Container, HTTP обертки

### Frontend (React + TypeScript)
- **Components**: Переиспользуемые UI компоненты
- **Pages**: Страницы приложения
- **Services**: API клиенты с Axios
- **Store**: Zustand для state management
- **Types**: TypeScript интерфейсы

## 🗄️ База данных

### PostgreSQL Schema
- `users` - Система авторизации
- `players` - Игроки из Brawl Stars
- `seasons` - Сезоны/периоды
- `scores` - Рейтинги за сезон
- `score_history` - Аудит изменений

### Индексы для производительности
- `idx_players_trophies` (DESC) - быстрая сортировка по трофеям
- `idx_players_nickname` - быстрый поиск по никнейму
- `idx_scores_total_score` (DESC) - сортировка по очкам

## 🔌 API Endpoints

### Public
- `GET /api/health` - Проверка состояния сервисов
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Вход
- `GET /api/leaderboards/global` - Глобальный лидерборд (поддерживает `limit`, `offset`, `region`, `seasonId`/`season` для фильтрации по сезону)
- `GET /api/players/search` - Поиск игроков

### Protected (JWT required)
- `GET /api/players/me` - Профиль текущего пользователя
- `POST /api/players/link` - Привязка Brawl Stars аккаунта
- `GET /api/players/me/stats` - Статистика
- `GET /api/players/me/history` - История изменений
- `POST /api/scores` / `PUT /api/scores` - Создание или обновление очков игрока за сезон (тело: `playerId`, `seasonId`/`season` при необходимости, `totalScore`, `wins`, `losses`)

## 🎨 Frontend Features

### ✅ Реализовано
- 🔐 Аутентификация (регистрация/вход)
- 📊 Глобальный лидерборд с пагинацией
- 🔍 Поиск игроков с debounce
- 📱 Responsive дизайн
- 🎨 Современный UI с Tailwind CSS
- ⚡ Zustand state management
- 🔄 Автоматическое обновление токенов
- 🏆 Визуализация рейтингов (топ-1, топ-3, топ-10)

### 🚧 В разработке
- 📈 Графики прогресса
- 🔔 Уведомления
- 🌍 Региональные лидерборды
- 📊 Детальная статистика

## 🛠️ Разработка

### Backend команды
```bash
# Установка зависимостей
composer install

# Проверка кода
composer cs-check          # Проверка стиля
composer cs-fix            # Исправление стиля
composer phpstan           # Статический анализ
composer test             # Запуск тестов
```

### Frontend команды
```bash
# Установка зависимостей
npm install

# Разработка
npm run dev              # Запуск dev сервера (только localhost процесса)
npm run dev:host         # Dev сервер, доступный извне контейнера/WSL на порту 5173
npm run build            # Сборка для production
npm run preview          # Предпросмотр сборки
npm run test             # Запуск тестов
```

### Docker команды
```bash
# Запуск всех сервисов
docker-compose up -d

# Просмотр логов
docker-compose logs -f

# Перезапуск
docker-compose restart

# Остановка
docker-compose down

# Полная очистка
docker-compose down -v
```

## 🔧 Конфигурация

### Backend (.env)
```env
DB_HOST=postgres
DB_PORT=5432
DB_NAME=brawl_stars
DB_USER=postgres
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

JWT_SECRET=your-super-secret-key
BRAWL_STARS_API_KEY=your-api-key

APP_CORS_ORIGIN=http://localhost:5173
```

### Frontend (.env)
```env
VITE_API_URL=http://localhost/api
VITE_JWT_REFRESH_INTERVAL=5
VITE_APP_NAME=Brawl Stars Leaderboard
```

## 🧪 Тестирование

### Backend Tests
- **Unit**: кейсы для сервисов и use case сценариев (авторизация, глобальный лидерборд, привязка игрока)
- **Integration**: контроллеры/роутер для `/leaderboards/global`, `/auth/login`, `/players/:id` через тестовый DI-контейнер с in-memory репозиториями

```bash
# Установка зависимостей для тестов
composer install

# Unit тесты
vendor/bin/phpunit --testsuite Unit

# Integration тесты
vendor/bin/phpunit --testsuite Integration

# Coverage отчет
vendor/bin/phpunit --coverage-html coverage/
```

### Frontend Tests
```bash
# Component тесты
npm test

# E2E тесты (когда будут реализованы)
npm run test:e2e
```

## 📊 Performance

### Оптимизации
- **База данных**: Индексы для быстрых запросов
- **Кеширование**: Redis для лидербордов (5 мин TTL)
- **Frontend**: Code splitting, lazy loading
- **API**: Rate limiting, CORS

### Целевые метрики
- Лидерборд: < 200ms (без кеша), < 50ms (с кешем)
- Поиск: < 100ms
- First Contentful Paint: < 2s

## 🔒 Безопасность

### Реализовано
- ✅ JWT аутентификация
- ✅ Rate limiting
- ✅ CORS конфигурация
- ✅ Валидация входных данных
- ✅ Хеширование паролей (Argon2id)
- ✅ SQL injection prevention
- ✅ XSS prevention

## 🚀 Развертывание

### Production
```bash
# Сборка frontend
cd leaderboard-frontend
npm run build

# Запуск с production конфигом
docker-compose -f docker-compose.prod.yml up -d
```

### Переменные окружения для production
- `APP_ENV=production`
- `APP_DEBUG=false`
- Сменить все пароли и секретные ключи
- Настроить HTTPS

## 🤝 Contributing

1. Fork проекта
2. Создать feature ветку
3. Внести изменения
4. Проверить код (`composer cs-check`, `npm test`)
5. Создать Pull Request

## 🗂️ Документация

- Аналитический обзор и требования: [docs/overview.md](./docs/overview.md)
- Диаграммы (Use-Case, архитектура, ER, последовательности): [docs/diagrams](./docs/diagrams)
- Черновик презентации: [docs/presentation.md](./docs/presentation.md)

## 📄 Лицензия

MIT License - см. файл LICENSE

## 🆘 Поддержка

Если возникли проблемы:

1. Проверьте [Troubleshooting](./docs/TROUBLESHOOTING.md)
2. Проверьте [FAQ](./docs/FAQ.md)
3. Создайте Issue в GitHub

---

**Разработано с ❤️ для курсовой работы по веб-разработке**

*Технологический стек: PHP 8.2, PostgreSQL 15, Redis 7, React 18, TypeScript, Tailwind CSS, Docker*