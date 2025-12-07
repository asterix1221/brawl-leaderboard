-- Create players table
CREATE TABLE IF NOT EXISTS players (
    id VARCHAR(20) PRIMARY KEY,
    nickname VARCHAR(100) NOT NULL,
    total_trophies INTEGER DEFAULT 0,
    region VARCHAR(10),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_players_trophies ON players(total_trophies DESC);
CREATE INDEX IF NOT EXISTS idx_players_nickname ON players(nickname);

