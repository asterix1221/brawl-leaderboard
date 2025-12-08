@echo off
echo 🚀 Starting database migrations for Brawl Stars Leaderboard...

REM Wait for PostgreSQL to be ready
echo ⏳ Waiting for PostgreSQL to be ready...
:wait_postgres
docker exec brawl-postgres pg_isready -U postgres >nul 2>&1
if errorlevel 1 (
    echo PostgreSQL is unavailable - sleeping
    timeout /t 2 /nobreak >nul
    goto wait_postgres
)
echo ✅ PostgreSQL is ready!

echo 🔐 Ensuring pgcrypto extension is installed...
docker exec brawl-postgres psql -U postgres -d brawl_stars -c "CREATE EXTENSION IF NOT EXISTS pgcrypto;"
if errorlevel 1 (
    echo ❌ Failed to create pgcrypto extension
    pause
    exit /b 1
)

REM Apply migrations in order
echo 📋 Applying migrations...

docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations\001_create_users_table.sql
if errorlevel 1 (
    echo ❌ Failed to apply 001_create_users_table.sql
    pause
    exit /b 1
)
echo ✅ 001_create_users_table.sql applied successfully

docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations\002_create_players_table.sql
if errorlevel 1 (
    echo ❌ Failed to apply 002_create_players_table.sql
    pause
    exit /b 1
)
echo ✅ 002_create_players_table.sql applied successfully

docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations\003_create_seasons_table.sql
if errorlevel 1 (
    echo ❌ Failed to apply 003_create_seasons_table.sql
    pause
    exit /b 1
)
echo ✅ 003_create_seasons_table.sql applied successfully

docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations\004_create_scores_table.sql
if errorlevel 1 (
    echo ❌ Failed to apply 004_create_scores_table.sql
    pause
    exit /b 1
)
echo ✅ 004_create_scores_table.sql applied successfully

docker exec brawl-postgres psql -U postgres -d brawl_stars < migrations\005_create_score_history_table.sql
if errorlevel 1 (
    echo ❌ Failed to apply 005_create_score_history_table.sql
    pause
    exit /b 1
)
echo ✅ 005_create_score_history_table.sql applied successfully

REM Verify tables were created
echo 🔍 Verifying created tables...
docker exec brawl-postgres psql -U postgres -d brawl_stars -c "\dt"

echo.
echo 🎉 All migrations completed successfully!
echo.
echo 📊 Database schema:
echo   - users (authentication)
echo   - players (Brawl Stars players)
echo   - seasons (seasons/periods)
echo   - scores (player scores per season)
echo   - score_history (audit log)
echo.
echo 🌐 API is now available at: http://localhost/api/health
echo.
pause