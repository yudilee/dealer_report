<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Reports Dashboard</title>
    {{-- No Vite needed - all styles are inline --}}
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #22c55e;
            --accent-yellow: #eab308;
            --border-color: #334155;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .top-bar {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .top-bar h1 {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-tabs {
            display: flex;
            gap: 4px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 10px;
            padding: 4px;
        }

        .nav-tab {
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-tab:hover { color: var(--text-primary); background: rgba(59, 130, 246, 0.1); }
        .nav-tab.active {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--accent-blue);
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
            box-shadow: 0 2px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-green {
            background: var(--accent-green);
            color: white;
        }
        .btn-green:hover {
            background: #16a34a;
            box-shadow: 0 2px 12px rgba(34, 197, 94, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        .btn-outline:hover {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .content { padding: 16px 24px; }

        /* Spreadsheet table */
        .table-wrapper {
            position: relative;
            overflow: auto;
            max-height: calc(100vh - 120px);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
        }

        .report-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 100%;
            font-size: 0.78rem;
        }

        .report-table th,
        .report-table td {
            padding: 6px 10px;
            white-space: nowrap;
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .report-table th {
            background: #1a2744;
            font-weight: 600;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .report-table th.corner {
            z-index: 30;
            left: 0;
        }

        /* Frozen first 2 columns */
        .report-table td:nth-child(1),
        .report-table td:nth-child(2) {
            position: sticky;
            z-index: 10;
            background: var(--bg-card);
        }
        .report-table td:nth-child(1) { left: 0; }
        .report-table td:nth-child(2) { left: 30px; }

        .report-table th:nth-child(1),
        .report-table th:nth-child(2) {
            position: sticky;
            left: 0;
            z-index: 30;
        }
        .report-table th:nth-child(2) { left: 30px; }

        /* Row styles */
        .row-section {
            background: #2d3748 !important;
            font-weight: 700;
            color: var(--accent-blue);
            font-size: 0.82rem;
        }
        .row-section td { background: #2d3748 !important; }

        .row-revenue { font-weight: 600; }
        .row-revenue td { background: rgba(34, 197, 94, 0.08) !important; }

        .row-metric td { background: rgba(234, 179, 8, 0.06) !important; }

        .row-total { font-weight: 700; }
        .row-total td { background: rgba(59, 130, 246, 0.1) !important; }

        .row-subsection { font-weight: 600; color: var(--accent-blue); font-size: 0.8rem; }

        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .pct { text-align: right; color: var(--accent-green); }

        /* Import page */
        .import-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 16px;
        }

        .import-section {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border-color);
        }

        .import-section h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--accent-blue);
        }

        .file-slot {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .file-slot:last-child { border-bottom: none; }

        .file-slot label {
            font-size: 0.85rem;
            min-width: 100px;
            color: var(--text-secondary);
        }

        .file-slot input[type="file"] {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .file-slot .status {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .status-imported {
            background: rgba(34, 197, 94, 0.15);
            color: var(--accent-green);
        }

        select, input[type="number"] {
            background: var(--bg-dark);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.85rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--accent-green);
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }

        .period-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Modern custom scrollbar — both vertical & horizontal */
        .table-wrapper {
            scrollbar-width: thin;
            scrollbar-color: rgba(99, 130, 191, 0.35) transparent;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.4);
            border-radius: 8px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(99, 130, 191, 0.45) 0%, rgba(59, 130, 246, 0.35) 100%);
            border-radius: 8px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(99, 130, 191, 0.7) 0%, rgba(59, 130, 246, 0.6) 100%);
            background-clip: padding-box;
        }

        .table-wrapper::-webkit-scrollbar-corner {
            background: rgba(15, 23, 42, 0.4);
            border-radius: 0 0 8px 0;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="top-bar">
        <h1>⬡ Dealer Reports</h1>
        <div class="nav-tabs">
            <a class="nav-tab {{ request()->routeIs('report.weekly') ? 'active' : '' }}"
               href="{{ route('report.weekly') }}">WDR</a>
            <a class="nav-tab {{ request()->routeIs('report.monthly') ? 'active' : '' }}"
               href="{{ route('report.monthly') }}">MDR</a>
            <a class="nav-tab {{ request()->routeIs('import.page') ? 'active' : '' }}"
               href="{{ route('import.page') }}">Import</a>
        </div>
        <div class="actions">
            @yield('actions')
        </div>
    </div>
    <div class="content">
        @yield('content')
    </div>
    @include('partials.password-modal')
</body>
</html>
