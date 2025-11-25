@extends('nav_bar')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('admin-title', 'Bin Statistics')

@section('admin-content')
    <div class="container">
        <div class="bin-stats-container">
            <div class="bin-desc">
                <h2>{{ ucfirst($bin->type) }} Bin</h2>
                <p><strong>Location:</strong> {{ $bin->level->name ?? 'Unknown Level' }}</p>
                <p><strong>Floor:</strong> {{ $bin->level->floor_number ?? 'N/A' }}</p>

                <div class="bin-info">
                    <p><strong>Current Weight:</strong> {{ $weight }} kg</p>
                    <p><strong>Capacity:</strong> {{ $bin->capacity }} kg</p>
                    <p><strong>Fill Level:</strong> {{ $percent }}%</p>
                    <p><strong>Status:</strong>
                        <span class="status {{ strtolower(str_replace(' ', '-', $status)) }}">{{ $status }}</span>
                    </p>
                </div>

                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: {{ $percent }}%;"></div>
                </div>
            </div>
            <div class="bin-chart">
                <h3>Historical Waste Data</h3>
                <canvas id="wasteHistoryChart" width="400" height="180"></canvas>
            </div>
        </div>
        <hr width="95%">
        <div class="bin-details-container">
            <h3>History (Latest Waste Logs)</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Weight (kg)</th>
                        <th>Fill Level (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bin->wasteLevels->sortByDesc('created_at') as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $log->weight }}</td>
                            <td>{{ round(($log->weight / ($bin->capacity ?: 1)) * 100, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No history available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const history = @json($history ?? []);
        const labels = history.map(h => h.date);
        const weights = history.map(h => h.weight);
        const percents = history.map(h => h.percent);

        const ctx = document.getElementById('wasteHistoryChart');
        if (labels.length > 0) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Weight (kg)',
                            data: weights,
                            borderColor: '#4caf50',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            tension: 0.3
                        },
                        {
                            label: 'Fill Level (%)',
                            data: percents,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    </script>

    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .bin-stats-container {
            display: flex;
            width: 95%;
            gap: 2vh;
            margin-top: 2vh;
            margin-bottom: 2vh;
            justify-content: space-between;
        }

        .bin-desc {
            flex: 1;
            width: 40%;
            background: #fff;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .bin-chart {
            flex: 1;
            width: 65%;
            background: #fff;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .bin-details-container {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 95%;
            margin: auto;
            margin-top: 2vh;
            align-items: center;
        }

        .progress-bar-container {
            background: #eee;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-bar {
            background: #4caf50;
            height: 100%;
            transition: width 0.4s ease;
        }

        .status.full {
            color: red;
        }

        .status.almost-full {
            color: orange;
        }

        .status.half {
            color: gold;
        }

        .status.empty {
            color: gray;
        }

        .history-table {
            width: 95%;
            border-collapse: collapse;
            margin-left: 2vh;
            margin-right: 2vh;
            margin-bottom: 4vh;
        }   

        .history-table th,
        .history-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
    </style>
@endsection