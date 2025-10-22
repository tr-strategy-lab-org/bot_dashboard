CREATE TABLE IF NOT EXISTS strategies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    strategy_name VARCHAR(100) UNIQUE NOT NULL,
    nav DECIMAL(20,8) NOT NULL,
    last_update DATETIME NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_strategy_name ON strategies(strategy_name);
CREATE INDEX IF NOT EXISTS idx_last_update ON strategies(last_update);
