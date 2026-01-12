<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0d1117;
            color: #c9d1d9;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #58a6ff;
        }
        
        .subtitle {
            color: #8b949e;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: #161b22;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #30363d;
            border-left: 4px solid #238636;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .metric-value {
            font-size: 42px;
            font-weight: bold;
            color: #238636;
            margin-bottom: 8px;
            font-variant-numeric: tabular-nums;
        }
        
        .metric-label {
            font-size: 13px;
            color: #8b949e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .metric-subtext {
            font-size: 12px;
            color: #6e7681;
            margin-top: 8px;
        }
        
        /* Status colors */
        .warning {
            border-left-color: #d29922;
        }
        
        .warning .metric-value {
            color: #d29922;
        }
        
        .critical {
            border-left-color: #f85149;
        }
        
        .critical .metric-value {
            color: #f85149;
        }
        
        .chart-container {
            background: #161b22;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #30363d;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #c9d1d9;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            background: #238636;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #2ea043;
        }
        
        .btn-secondary {
            background: #21262d;
            border: 1px solid #30363d;
        }
        
        .btn-secondary:hover {
            background: #30363d;
        }
        
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #238636;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .stats-table {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        .stats-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .stats-table th {
            background: #21262d;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            color: #8b949e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stats-table td {
            padding: 12px;
            border-top: 1px solid #30363d;
            font-size: 14px;
        }
        
        .stats-table tr:hover {
            background: #21262d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="status-indicator"></span>Performance Dashboard</h1>
        <p class="subtitle">Real-time system performance monitoring • Auto-refresh every 5 seconds</p>
        
        <!-- Metric Cards -->
        <div class="metric-grid">
            <div class="metric-card" id="db-card">
                <div class="metric-value" id="db-response">--</div>
                <div class="metric-label">Database Response Time</div>
                <div class="metric-subtext">Last query execution</div>
            </div>
            
            <div class="metric-card" id="memory-card">
                <div class="metric-value" id="memory-usage">--</div>
                <div class="metric-label">Memory Usage</div>
                <div class="metric-subtext">Current PHP process</div>
            </div>
            
            <div class="metric-card" id="clustering-card">
                <div class="metric-value" id="clustering-time">--</div>
                <div class="metric-label">Last Clustering Time</div>
                <div class="metric-subtext">Total execution duration</div>
            </div>
            
            <div class="metric-card">
                <div class="metric-value" id="total-customers">--</div>
                <div class="metric-label">Total Customers</div>
                <div class="metric-subtext">In database</div>
            </div>
            
            <div class="metric-card">
                <div class="metric-value" id="peak-memory">--</div>
                <div class="metric-label">Peak Memory</div>
                <div class="metric-subtext">Maximum allocation</div>
            </div>
            
            <div class="metric-card">
                <div class="metric-value" id="php-version">--</div>
                <div class="metric-label">PHP Version</div>
                <div class="metric-subtext">Runtime environment</div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="chart-container">
            <div class="chart-title">Database Response Time</div>
            <div class="chart-wrapper">
                <canvas id="db-chart"></canvas>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="chart-title">Memory Usage</div>
            <div class="chart-wrapper">
                <canvas id="memory-chart"></canvas>
            </div>
        </div>
        
        <!-- Performance Statistics Table -->
        <div class="stats-table">
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Current</th>
                        <th>Average</th>
                        <th>Peak</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="stats-tbody">
                    <tr>
                        <td colspan="5" style="text-align: center; color: #8b949e;">Loading statistics...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Actions -->
        <div class="actions">
            <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
            <button class="btn" onclick="clearHistory()">Clear History</button>
            <button class="btn btn-secondary" onclick="exportMetrics()">Export Metrics</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Performance data history
        const metricsHistory = {
            labels: [],
            dbResponse: [],
            memory: [],
            peakMemory: []
        };
        
        let stats = {
            dbResponse: { current: 0, sum: 0, count: 0, peak: 0 },
            memory: { current: 0, sum: 0, count: 0, peak: 0 }
        };

        // Initialize charts
        const dbChart = new Chart(document.getElementById('db-chart'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Response Time (ms)',
                    data: [],
                    borderColor: '#238636',
                    backgroundColor: 'rgba(35, 134, 54, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#30363d' },
                        ticks: { color: '#8b949e' }
                    },
                    x: {
                        grid: { color: '#30363d' },
                        ticks: { color: '#8b949e' }
                    }
                }
            }
        });

        const memoryChart = new Chart(document.getElementById('memory-chart'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Memory (MB)',
                    data: [],
                    borderColor: '#58a6ff',
                    backgroundColor: 'rgba(88, 166, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#30363d' },
                        ticks: { color: '#8b949e' }
                    },
                    x: {
                        grid: { color: '#30363d' },
                        ticks: { color: '#8b949e' }
                    }
                }
            }
        });

        // Fetch performance metrics
        async function fetchMetrics() {
            try {
                const response = await fetch('performance_metrics.php');
                const data = await response.json();
                
                // Update metric cards
                updateMetricCard('db-response', data.db_response_time, 'ms', 'db-card', 50, 100);
                updateMetricCard('memory-usage', data.memory_usage, 'MB', 'memory-card', 128, 200);
                updateMetricCard('clustering-time', data.last_clustering_time || 0, 's', 'clustering-card', 10, 20);
                document.getElementById('total-customers').textContent = formatNumber(data.total_customers || 0);
                updateMetricCard('peak-memory', data.memory_peak, 'MB', null, 0, 0);
                document.getElementById('php-version').textContent = data.php_version || '--';
                
                // Update history
                const now = new Date().toLocaleTimeString();
                metricsHistory.labels.push(now);
                metricsHistory.dbResponse.push(data.db_response_time);
                metricsHistory.memory.push(data.memory_usage);
                
                // Keep last 20 data points
                if (metricsHistory.labels.length > 20) {
                    metricsHistory.labels.shift();
                    metricsHistory.dbResponse.shift();
                    metricsHistory.memory.shift();
                }
                
                // Update charts
                dbChart.data.labels = metricsHistory.labels;
                dbChart.data.datasets[0].data = metricsHistory.dbResponse;
                dbChart.update('none');
                
                memoryChart.data.labels = metricsHistory.labels;
                memoryChart.data.datasets[0].data = metricsHistory.memory;
                memoryChart.update('none');
                
                // Update statistics
                updateStatistics(data);
                
            } catch (error) {
                console.error('Error fetching metrics:', error);
            }
        }

        function updateMetricCard(elementId, value, unit, cardId, warningThreshold, criticalThreshold) {
            const element = document.getElementById(elementId);
            const formattedValue = typeof value === 'number' ? value.toFixed(2) : value;
            element.textContent = formattedValue + (unit ? ' ' + unit : '');
            
            if (cardId) {
                const card = document.getElementById(cardId);
                card.className = 'metric-card';
                
                if (value > criticalThreshold) {
                    card.classList.add('critical');
                } else if (value > warningThreshold) {
                    card.classList.add('warning');
                }
            }
        }

        function updateStatistics(data) {
            // Update running statistics
            stats.dbResponse.current = data.db_response_time;
            stats.dbResponse.sum += data.db_response_time;
            stats.dbResponse.count++;
            stats.dbResponse.peak = Math.max(stats.dbResponse.peak, data.db_response_time);
            
            stats.memory.current = data.memory_usage;
            stats.memory.sum += data.memory_usage;
            stats.memory.count++;
            stats.memory.peak = Math.max(stats.memory.peak, data.memory_usage);
            
            // Update table
            const tbody = document.getElementById('stats-tbody');
            tbody.innerHTML = `
                <tr>
                    <td>DB Response Time</td>
                    <td>${stats.dbResponse.current.toFixed(2)} ms</td>
                    <td>${(stats.dbResponse.sum / stats.dbResponse.count).toFixed(2)} ms</td>
                    <td>${stats.dbResponse.peak.toFixed(2)} ms</td>
                    <td>${getStatusBadge(stats.dbResponse.current, 50, 100)}</td>
                </tr>
                <tr>
                    <td>Memory Usage</td>
                    <td>${stats.memory.current.toFixed(2)} MB</td>
                    <td>${(stats.memory.sum / stats.memory.count).toFixed(2)} MB</td>
                    <td>${stats.memory.peak.toFixed(2)} MB</td>
                    <td>${getStatusBadge(stats.memory.current, 128, 200)}</td>
                </tr>
                <tr>
                    <td>Total Samples</td>
                    <td>${stats.dbResponse.count}</td>
                    <td>--</td>
                    <td>--</td>
                    <td><span style="color: #238636;">●</span> Active</td>
                </tr>
            `;
        }

        function getStatusBadge(value, warning, critical) {
            if (value > critical) {
                return '<span style="color: #f85149;">●</span> Critical';
            } else if (value > warning) {
                return '<span style="color: #d29922;">●</span> Warning';
            } else {
                return '<span style="color: #238636;">●</span> Good';
            }
        }

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function clearHistory() {
            if (confirm('Clear all performance history?')) {
                metricsHistory.labels = [];
                metricsHistory.dbResponse = [];
                metricsHistory.memory = [];
                
                stats = {
                    dbResponse: { current: 0, sum: 0, count: 0, peak: 0 },
                    memory: { current: 0, sum: 0, count: 0, peak: 0 }
                };
                
                dbChart.data.labels = [];
                dbChart.data.datasets[0].data = [];
                dbChart.update();
                
                memoryChart.data.labels = [];
                memoryChart.data.datasets[0].data = [];
                memoryChart.update();
                
                alert('History cleared!');
            }
        }

        function exportMetrics() {
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Timestamp,DB Response (ms),Memory (MB)\n"
                + metricsHistory.labels.map((label, i) => 
                    `${label},${metricsHistory.dbResponse[i]},${metricsHistory.memory[i]}`
                ).join("\n");
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `performance_metrics_${Date.now()}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Initial fetch and set interval
        fetchMetrics();
        setInterval(fetchMetrics, 5000);
    </script>
</body>
</html>