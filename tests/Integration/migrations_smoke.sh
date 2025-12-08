#!/bin/bash
set -euo pipefail

echo "🚦 Running migration smoke test..."

# Run migrations (ensures pgcrypto extension is created)
./migrate.sh

echo "🔍 Verifying pgcrypto extension..."
EXT_PRESENT=$(docker exec brawl-postgres psql -U postgres -d brawl_stars -tAc "SELECT 1 FROM pg_extension WHERE extname='pgcrypto';")
if [[ "$EXT_PRESENT" != "1" ]]; then
    echo "❌ pgcrypto extension is missing after migrations"
    exit 1
fi

echo "✅ pgcrypto extension detected. Migrations are healthy."
