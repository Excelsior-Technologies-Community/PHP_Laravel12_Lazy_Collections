<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lazy Collections Demo - Laravel 12</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .header p {
            color: #4a5568;
            font-size: 14px;
        }

        /* Grid Layout */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }

        .card h3 {
            color: #2d3748;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3182ce;
            display: inline-block;
        }

        .card p {
            color: #718096;
            font-size: 13px;
            margin: 12px 0;
            line-height: 1.5;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 12px 0;
        }

        .btn {
            display: inline-block;
            background: #3182ce;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #2c5282;
        }

        .btn-secondary {
            background: #48bb78;
        }

        .btn-secondary:hover {
            background: #38a169;
        }

        .btn-outline {
            background: white;
            color: #3182ce;
            border: 1px solid #3182ce;
        }

        .btn-outline:hover {
            background: #ebf8ff;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }

        /* Response Area */
        .response-area {
            background: #2d3748;
            color: #e2e8f0;
            padding: 12px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            max-height: 300px;
            margin-top: 12px;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            min-width: 200px;
        }

        .spinner {
            border: 3px solid #e2e8f0;
            border-top: 3px solid #3182ce;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading p {
            color: #2d3748;
            font-size: 13px;
        }

        /* Stats Row */
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stat-box {
            text-align: center;
            flex: 1;
            min-width: 60px;
        }

        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #3182ce;
        }

        .stat-label {
            font-size: 11px;
            color: #718096;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 25px;
            color: #718096;
            font-size: 12px;
            border-top: 1px solid #e2e8f0;
            margin-top: 20px;
        }

        /* Badge */
        .badge {
            display: inline-block;
            background: #edf2f7;
            color: #2d3748;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
            font-weight: normal;
        }

        .badge-new {
            background: #fed7d7;
            color: #c53030;
        }

        /* Link */
        .download-link {
            display: inline-block;
            background: #48bb78;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
            margin-top: 10px;
        }

        .download-link:hover {
            background: #38a169;
        }

        hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 22px;
            }
            .stats-row {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laravel 12 Lazy Collections</h1>
            <p>Efficient data handling | Low memory usage | Stream processing</p>
        </div>

        <!-- Row 1: Memory & Performance Tests -->
        <div class="grid">
            <div class="card">
                <h3>Memory Comparison</h3>
                <p>Compare memory usage between normal collection and lazy collection</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/memory-test', 'memoryResult')">Run Test</button>
                </div>
                <div id="memoryResult" class="response-area">Click "Run Test" to see results</div>
            </div>

            <div class="card">
                <h3>Performance Test</h3>
                <p>Compare execution time and memory usage (10k records)</p>
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="loadData('/performance-test', 'performanceResult')">Run Test</button>
                </div>
                <div id="performanceResult" class="response-area">Click "Run Test" to see results</div>
            </div>

            <div class="card">
                <h3>Batch Update</h3>
                <p>Update records in batches without memory issues</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/batch-update', 'batchUpdateResult')">Run Update</button>
                </div>
                <div id="batchUpdateResult" class="response-area">Click "Run Update" to see results</div>
            </div>
        </div>

        <!-- Row 2: Filter & Statistics -->
        <div class="grid">
            <div class="card">
                <h3>Advanced Filter</h3>
                <p>Filter users with multiple conditions</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/advanced-filter/user', 'filterResult')">Filter: "user"</button>
                    <button class="btn btn-secondary" onclick="loadData('/advanced-filter?domain=gmail.com', 'filterResult')">Gmail Users</button>
                    <button class="btn btn-outline" onclick="loadData('/advanced-filter?domain=yahoo.com', 'filterResult')">Yahoo Users</button>
                </div>
                <div id="filterResult" class="response-area">Click a button to filter users</div>
            </div>

            <div class="card">
                <h3>Aggregate Statistics</h3>
                <p>Calculate statistics using map & reduce</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/aggregate-stats', 'aggregateResult')">Calculate Stats</button>
                </div>
                <div id="aggregateResult" class="response-area">Click "Calculate Stats" to see results</div>
            </div>

            <div class="card">
                <h3>Transform Pipeline</h3>
                <p>4-step data transformation pipeline</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/transform-pipeline', 'pipelineResult')">Run Pipeline</button>
                </div>
                <div id="pipelineResult" class="response-area">Click "Run Pipeline" to see results</div>
            </div>
        </div>

        <!-- Row 3: Notifications & File Processing -->
        <div class="grid">
            <div class="card">
                <h3>Email Simulation <span class="badge badge-new">Demo</span></h3>
                <p>Simulate email sending with rate limiting</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/send-notifications', 'emailResult')">Send Emails</button>
                </div>
                <div id="emailResult" class="response-area">Click "Send Emails" to simulate</div>
            </div>

            <div class="card">
                <h3>JSONL Processing <span class="badge badge-new">Demo</span></h3>
                <p>Process JSON Lines file line by line</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/process-jsonl', 'jsonlResult')">Process File</button>
                </div>
                <div id="jsonlResult" class="response-area">Click "Process File" to start</div>
            </div>

            <div class="card">
                <h3>Dashboard Stats</h3>
                <p>Real-time analytics dashboard</p>
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="loadData('/dashboard-stats', 'dashboardResult')">View Stats</button>
                </div>
                <div id="dashboardResult" class="response-area">Click "View Stats" to see analytics</div>
            </div>
        </div>

        <!-- Row 4: Pagination & Export -->
        <div class="grid">
            <div class="card">
                <h3>Lazy Pagination</h3>
                <p>Navigate through records without loading all</p>
                <div class="btn-group">
                    <button class="btn btn-sm" onclick="loadData('/lazy-paginate/1', 'paginationResult')">Page 1</button>
                    <button class="btn btn-sm btn-secondary" onclick="loadData('/lazy-paginate/2', 'paginationResult')">Page 2</button>
                    <button class="btn btn-sm" onclick="loadData('/lazy-paginate/3', 'paginationResult')">Page 3</button>
                </div>
                <div id="paginationResult" class="response-area">Click a page number</div>
            </div>

            <div class="card">
                <h3>CSV Export</h3>
                <p>Stream CSV export without memory issues</p>
                <a href="/export-csv" class="download-link">Download CSV</a>
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-number">Streaming</div>
                        <div class="stat-label">Low memory</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">Real-time</div>
                        <div class="stat-label">Download</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Data Stream</h3>
                <p>JSON stream output - opens in new tab</p>
                <a href="/stream-users" class="download-link" target="_blank">View Stream</a>
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-number">SSE</div>
                        <div class="stat-label">Live data</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">JSON</div>
                        <div class="stat-label">Format</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 5: Batch Processing -->
        <div class="grid">
            <div class="card">
                <h3>Batch Processing</h3>
                <p>Process records in batches with progress tracking</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/batch-process', 'batchResult')">Start Batch</button>
                </div>
                <div id="batchResult" class="response-area">Click "Start Batch" to process</div>
            </div>

            <div class="card">
                <h3>Combined Sources</h3>
                <p>Merge database + external data sources</p>
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="loadData('/combined-sources', 'combinedResult')">Merge Sources</button>
                </div>
                <div id="combinedResult" class="response-area">Click "Merge Sources" to combine</div>
            </div>

            <div class="card">
                <h3>Tap Progress</h3>
                <p>Track processing progress in laravel.log</p>
                <div class="btn-group">
                    <button class="btn" onclick="loadData('/tap-progress', 'tapResult')">Track Progress</button>
                </div>
                <div id="tapResult" class="response-area">Check storage/logs/laravel.log</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Laravel 12 Lazy Collections Demo | cursor() | chunk() | LazyCollection</p>
            <p style="margin-top: 8px;">Processing large datasets efficiently - one record at a time</p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading" class="loading">
        <div class="spinner"></div>
        <p>Processing data...</p>
    </div>

    <script>
        async function loadData(url, resultElementId) {
            const loadingDiv = document.getElementById('loading');
            const resultDiv = document.getElementById(resultElementId);
            
            loadingDiv.style.display = 'block';
            resultDiv.innerHTML = 'Loading...';
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                resultDiv.innerHTML = JSON.stringify(data, null, 2);
            } catch (error) {
                resultDiv.innerHTML = 'Error: ' + error.message;
            } finally {
                loadingDiv.style.display = 'none';
            }
        }

        // Auto-load initial stats when page loads
        window.addEventListener('DOMContentLoaded', function() {
            loadData('/memory-test', 'memoryResult');
            loadData('/dashboard-stats', 'dashboardResult');
        });
    </script>
</body>
</html>