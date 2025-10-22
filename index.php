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

// Get database connection
$pdo = getPDO();

// Get all strategies
$strategies = getAllStrategies($pdo);

// Calculate total strategies
$totalStrategies = count($strategies);

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
                            <th>Last Update</th>
                            <th>Status</th>
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
                                <td><?php echo formatTimestamp($strategy['last_update']); ?></td>
                                <td>
                                    <span class="status-indicator">
                                        <?php echo $status['indicator']; ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php
                                        if ($status['minutes_old'] >= 0) {
                                            echo $status['minutes_old'] . ' min ago';
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
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
