@echo off
echo ========================================
echo   Brawl Stars Leaderboard Launcher
echo ========================================
echo.

REM Check if Docker Desktop is running
echo 🔍 Checking Docker Desktop...
docker info >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Desktop is not running!
    echo.
    echo Please start Docker Desktop and wait for it to fully load.
    echo Then run this script again.
    echo.
    pause
    exit /b 1
)

echo ✅ Docker Desktop is running!
echo.

REM Start Docker containers
echo 🚀 Starting Docker containers...
docker-compose up -d

if errorlevel 1 (
    echo ❌ Failed to start Docker containers!
    echo Check docker-compose.yml file and Docker Desktop.
    pause
    exit /b 1
)

echo ✅ Docker containers started!
echo.

REM Wait for services to be ready
echo ⏳ Waiting for services to be ready...
timeout /t 10 /nobreak >nul

REM Apply migrations
echo 📋 Applying database migrations...
call migrate.bat

if errorlevel 1 (
    echo ❌ Migration failed!
    pause
    exit /b 1
)

echo.
echo 🎉 Setup completed successfully!
echo.
echo 📊 Services status:
echo   🌐 API:        http://localhost/api/health
echo   🗄️  Database:   postgresql://localhost:5432/brawl_stars
echo   🔴 Redis:      redis://localhost:6379
echo   🐳 Docker:     docker-compose ps
echo.
echo 🌍 Frontend setup:
echo   cd leaderboard-frontend
echo   npm install
echo   npm run dev
echo.
echo 📝 Useful commands:
echo   docker-compose logs -f     (view logs)
echo   docker-compose restart      (restart services)
echo   docker-compose down        (stop services)
echo.
pause