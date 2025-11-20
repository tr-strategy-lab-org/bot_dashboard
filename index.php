<?php

/**
 * Hummingbot Dashboard
 * Main dashboard page showing strategy monitoring
 */

// Requires
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Get configuration
$config = include __DIR__ . '/config/config.php';

// Set timezone
date_default_timezone_set($config['timezone']);

// Debug mode
define('DEBUG_MODE', true);

// Get database connection
$pdo = getPDO();

// Get all strategies
$strategies = getAllStrategies($pdo);

if (DEBUG_MODE) {
    error_log('Dashboard loaded. Strategies count: ' . count($strategies));
}

// Calculate total strategies
$totalStrategies = count($strategies);

// Calculate total NAV and NAV-BTC
$totalNav = 0;
$totalNavBtc = 0;
foreach ($strategies as $strategy) {
    $totalNav += floatval($strategy['nav']);
    if ($strategy['nav_btc'] !== null) {
        $totalNavBtc += floatval($strategy['nav_btc']);
    }
}

// Get current time for display
$currentTime = new DateTime();
$currentTimeFormatted = $currentTime->format('d.m.Y H:i:s');

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safeOutput($config['dashboard_title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-fluid py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><?php echo safeOutput($config['dashboard_title']); ?></h1>
            </div>
            <div class="col-md-4 text-end">
                <div class="dashboard-info">
                    <p class="mb-0">
                        <small class="text-muted">
                            Active Strategies: <strong><?php echo $totalStrategies; ?></strong>
                        </small>
                    </p>
                    <p class="mb-0">
                        <small class="text-muted">
                            Last Update: <strong id="updateTime"><?php echo $currentTimeFormatted; ?></strong>
                        </small>
                    </p>
                </div>
            </div>
        </div>

        <?php if (DEBUG_MODE): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong>🐛 Debug Mode Aktiv</strong>
                    <ul class="mb-0 mt-2" style="font-size: 0.9em;">
                        <li>Strategien geladen: <strong><?php echo $totalStrategies; ?></strong></li>
                        <li>Total NAV (USD): <strong><?php echo formatNav($totalNav); ?></strong></li>
                        <li>Total NAV (BTC): <strong><?php echo formatNav($totalNavBtc); ?></strong></li>
                        <li>Timezone: <strong><?php echo $config['timezone']; ?></strong></li>
                        <li>Refresh-Interval: <strong><?php echo $config['refresh_interval']; ?></strong>s</li>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="total-nav-box">
                    <div class="total-nav-item">
                        <div class="total-nav-label">Total NAV (USD)</div>
                        <div class="total-nav-value" id="totalNav"><?php echo formatNav($totalNav); ?></div>
                    </div>
                    <div class="total-nav-item">
                        <div class="total-nav-label">Total NAV (BTC)</div>
                        <div class="total-nav-value" id="totalNavBtc"><?php echo formatNav($totalNavBtc); ?></div>
                    </div>
                </div>
            </div>
        </div>
. i
        <?php if (empty($strategies)): ?>
            <div class="alert alert-info" role="alert">
                No strategies available yet. Use the API to add strategy data.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Strategy</th>
                            <th>NAV</th>
                            <th>NAV-BTC</th>
                            <th>Fee Currency</th>
                            <th>Last Trade</th>
                            <th>LAST UPDATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($strategies as $strategy): ?>
                            <?php
                            $status = getDataStatus($strategy['last_update']);
                            $statusClass = 'status-' . $status['status'];
                            ?>
                            <tr class="<?php echo $statusClass; ?>">
                                <td><?php echo safeOutput($strategy['strategy_name']); ?></td>
                                <td>
                                    <code><?php echo formatNav($strategy['nav']); ?></code>
                                </td>
                                <td>
                                    <code>
                                        <?php
                                            if ($strategy['nav_btc'] !== null && $strategy['nav_btc'] !== '') {
                                                echo formatNav($strategy['nav_btc']);
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </code>
                                </td>
                                <td>
                                    <code>
                                        <?php
                                            $systemToken = (isset($strategy['system_token']) && $strategy['system_token'] !== null && $strategy['system_token'] !== '') ? trim($strategy['system_token']) : null;
                                            $feeCurrencyBalance = (isset($strategy['fee_currency_balance']) && $strategy['fee_currency_balance'] !== null && $strategy['fee_currency_balance'] !== '') ? $strategy['fee_currency_balance'] : null;
                                            $feeCurrencyBalanceUsd = (isset($strategy['fee_currency_balance_usd']) && $strategy['fee_currency_balance_usd'] !== null && $strategy['fee_currency_balance_usd'] !== '') ? $strategy['fee_currency_balance_usd'] : null;

                                            // Debug
                                            if (DEBUG_MODE && !empty($strategy['system_token'])) {
                                                error_log('DEBUG: system_token = ' . var_export($strategy['system_token'], true));
                                            }

                                            $hasFeeData = $feeCurrencyBalance !== null || $feeCurrencyBalanceUsd !== null;

                                            if ($hasFeeData) {
                                                $output = '';
                                                if ($systemToken !== null) {
                                                    $output .= safeOutput($systemToken);
                                                }
                                                if ($feeCurrencyBalance !== null) {
                                                    if ($output !== '') {
                                                        $output .= ' ';
                                                    }
                                                    $output .= rtrim(number_format($feeCurrencyBalance, 5, '.', ''), '0');
                                                }
                                                if ($feeCurrencyBalanceUsd !== null) {
                                                    $output .= ' (USD ' . round($feeCurrencyBalanceUsd) . ')';
                                                }
                                                echo $output;
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </code>
                                </td>
                                <td>
                                    <?php
                                        if ($strategy['last_trade'] !== null && $strategy['last_trade'] !== '') {
                                            $tradeStatus = getTradeStatus($strategy['last_trade']);
                                            echo '<span class="status-indicator">';
                                            echo $tradeStatus['indicator'];
                                            echo '</span>';
                                            echo '<small class="text-muted">';
                                            echo getTradeTimeDiff($strategy['last_trade']);
                                            echo '</small>';
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <span class="status-indicator">
                                        <?php echo $status['indicator']; ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php echo $status['time_diff']; ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js" data-refresh-interval="<?php echo $config['refresh_interval']; ?>"></script>
</body>
</html>
