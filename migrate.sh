#!/bin/bash

# Migration script for Brawl Stars Leaderboard
# This script applies all database migrations in order

echo "🚀 Starting database migrations for Brawl Stars Leaderboard..."

# Wait for PostgreSQL to be ready
echo "⏳ Waiting for PostgreSQL to be ready..."
until docker exec brawl-postgres pg_isready -U postgres; do
    echo "PostgreSQL is unavailable - sleeping"
    sleep 2
done

echo "✅ PostgreSQL is ready!"

# Ensure pgcrypto extension is available before applying migrations
echo "🔐 Ensuring pgcrypto extension is installed..."
docker exec brawl-postgres psql -U postgres -d brawl_stars -c "CREATE EXTENSION IF NOT EXISTS pgcrypto;"
if [ $? -ne 0 ]; then
    echo "❌ Failed to create pgcrypto extension"
    exit 1
fi

# Apply migrations in order
echo "📋 Applying migrations..."

MIGRATIONS=(
    "001_create_users_table.sql"
    "002_create_players_table.sql" 
    "003_create_seasons_table.sql"
    "004_create_scores_table.sql"
    "005_create_score_history_table.sql"
)

for migration in "${MIGRATIONS[@]}"; do
    echo "🔄 Applying $migration..."
    docker exec brawl-postgres psql -U postgres -d brawl_stars < "migrations/$migration"
    if [ $? -eq 0 ]; then
        echo "✅ $migration applied successfully"
    else
        echo "❌ Failed to apply $migration"
        exit 1
    fi
done

# Verify tables were created
echo "🔍 Verifying created tables..."
docker exec brawl-postgres psql -U postgres -d brawl_stars -c "\dt"

echo "🎉 All migrations completed successfully!"
echo ""
echo "📊 Database schema:"
echo "  - users (authentication)"
echo "  - players (Brawl Stars players)"
echo "  - seasons (seasons/periods)"
echo "  - scores (player scores per season)"
echo "  - score_history (audit log)"
echo ""
echo "🌐 API is now available at: http://localhost/api/health"