/**
 * Hummingbot Dashboard - Auto Refresh
 * Automatically refreshes dashboard content at configurable intervals
 */

(function () {
    'use strict';

    // Get configuration from data attribute
    const script = document.currentScript;
    const refreshInterval = parseInt(script.getAttribute('data-refresh-interval'), 10) || 60;

    // Update time element
    function updateTime() {
        const timeElement = document.getElementById('updateTime');
        if (timeElement) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();

            timeElement.textContent = `${day}.${month}.${year} ${hours}:${minutes}:${seconds}`;
        }
    }

    // Refresh dashboard content
    function refreshDashboard() {
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.classList.add('loading');

            fetch(window.location.href)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    // Parse the response HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Get the table element from new content
                    const newTable = doc.querySelector('.table');
                    const newInfo = doc.querySelector('.dashboard-info');
                    const newAlert = doc.querySelector('.alert');

                    // Replace table if it exists
                    const currentTable = document.querySelector('.table');
                    const currentAlert = document.querySelector('.alert');
                    const currentInfo = document.querySelector('.dashboard-info');

                    if (newTable && currentTable) {
                        // Fade out current table
                        currentTable.style.opacity = '0.5';
                        currentTable.style.transition = 'opacity 0.3s ease';

                        setTimeout(() => {
                            currentTable.outerHTML = newTable.outerHTML;
                            const updatedTable = document.querySelector('.table');
                            if (updatedTable) {
                                updatedTable.style.opacity = '0.5';
                                updatedTable.style.transition = 'opacity 0.3s ease';
                                setTimeout(() => {
                                    updatedTable.style.opacity = '1';
                                }, 50);
                            }
                        }, 150);
                    }

                    // Replace alert if status changed
                    if (newAlert && currentAlert) {
                        currentAlert.outerHTML = newAlert.outerHTML;
                    }

                    // Update dashboard info
                    if (newInfo && currentInfo) {
                        currentInfo.innerHTML = newInfo.innerHTML;
                    }

                    // Update time display
                    updateTime();

                    container.classList.remove('loading');
                })
                .catch(error => {
                    console.error('Error refreshing dashboard:', error);
                    container.classList.remove('loading');
                });
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function () {
        // Update time immediately
        updateTime();

        // Update time every second
        setInterval(updateTime, 1000);

        // Refresh dashboard at configured interval
        setInterval(refreshDashboard, refreshInterval * 1000);
    });

    // Refresh on page visibility change
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshDashboard();
        }
    });
})();
