-- Create score_history table
CREATE TABLE IF NOT EXISTS score_history (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    score_id UUID NOT NULL,
    player_id VARCHAR(20) NOT NULL,
    old_score INTEGER,
    new_score INTEGER,
    change_reason VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (score_id) REFERENCES scores(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_score_history_player ON score_history(player_id);
CREATE INDEX IF NOT EXISTS idx_score_history_created ON score_history(created_at DESC);

