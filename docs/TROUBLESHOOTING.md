# Troubleshooting

## Common startup issues

### Containers exit immediately
- Ensure Docker Desktop/daemon is running.
- Remove old volumes if the database schema changed:
  ```bash
  docker-compose down -v
  docker-compose up -d
  ```
- Verify that ports `80`, `5432`, and `6379` are free before running the stack.

### Database connection errors
- Confirm `.env` contains the correct credentials (`DB_HOST=postgres`, `DB_USER`, `DB_PASSWORD`).
- Check PostgreSQL logs:
  ```bash
  docker-compose logs postgres
  ```
- Reapply migrations if the schema is missing:
  ```bash
  ./migrate.sh
  ```

### Redis not reachable
- Inspect the Redis container health:
  ```bash
  docker-compose logs redis
  ```
- If health checks keep failing, reset the container data:
  ```bash
  docker-compose down redis
  docker-compose up -d redis
  ```

### PHP dependencies missing
- Install Composer dependencies inside the PHP container when volumes are mounted:
  ```bash
  docker-compose exec php-fpm composer install
  ```
- For production images, install without dev tools before building:
  ```bash
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
  ```

### Frontend build not served
- Build the frontend before starting the production compose file so `leaderboard-frontend/dist` exists:
  ```bash
  cd leaderboard-frontend
  npm install
  npm run build
  ```
- After a rebuild, restart nginx to pick up new static files:
  ```bash
  docker-compose -f docker-compose.prod.yml restart nginx
  ```

### `/api/health` returns 404
- Make sure nginx is using the custom config from the repo. The compose file mounts
  `./docker/nginx/nginx.conf` into the container, so verify it inside the running
  container:
  ```bash
  docker compose exec nginx cat /etc/nginx/conf.d/default.conf
  ```
  The `/api` block must contain `try_files $uri /index.php$is_args$args;` so that
  requests are forwarded to PHP instead of being treated as static files.
- If the config looks correct, reload nginx to apply any recent changes:
  ```bash
  docker compose exec nginx nginx -s reload
  ```
- Ensure PHP-FPM is reachable from nginx. A quick check is to hit the PHP status
  page from inside the nginx container:
  ```bash
  docker compose exec nginx wget -qO- http://php-fpm:9000
  ```
  If this fails, restart both containers so the fastcgi upstream resolves
  properly:
  ```bash
  docker compose restart nginx php-fpm
  ```

## Health checks
- API health endpoint: `curl http://localhost/api/health`
- Database readiness (from host): `pg_isready -h localhost -p 5432 -U postgres`
- Redis ping: `redis-cli -h localhost -p 6379 ping`
