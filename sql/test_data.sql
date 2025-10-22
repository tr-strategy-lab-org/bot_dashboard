-- Test data for Hummingbot Dashboard

INSERT INTO strategies (strategy_name, nav, last_update) VALUES
('btc_usdt_market_making', 10250.45678, datetime('now', '-2 minutes')),
('eth_usdt_arbitrage', 5420.12345, datetime('now', '-7 minutes')),
('bnb_btc_grid_trading', 8750.99887, datetime('now', '-20 minutes'));
