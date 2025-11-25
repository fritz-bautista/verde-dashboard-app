@extends('nav_bar')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.4.0"></script>
@endsection

<div id="offline-banner" style="
        display: none;
        background-color: #ff4c4c;
        color: #fff;
        text-align: center;
        padding: 10px;
        font-weight: bold;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 9999;
    ">
    ⚠️ No internet connection. Can’t load data.
</div>

@section('admin-title', 'Welcome back, Fritz!')

@section('admin-content')
    <div class="dashboard-container">
        <div class="waste-reports">
            <div class="waste-bin-report">
                <div class="key-performance-indicators">
                    <div class="kpi-item1">
                        <p>Recycled Bottles This Month</p>
                        <h2>{{ number_format($totalBottle, 2) }} Kg</h2>
                    </div>
                    <div class="kpi-item2">
                        <p>Recycled Paper This Month</p>
                        <h2>{{ number_format($totalPaper, 2) }} Kg</h2>
                    </div>
                </div>
                <div class="options">
                    <h1>Recycling Bin Status</h1>
                    <div class="filter-options">
                        <label for="bin-type">Floor:</label>
                        <select id="bin-type">
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="waste-cluster-cards" id="bin-container">
                    @php
                        $level = $levels->first();
                        $binTypes = ['Recyclable - Bottled Water', 'Recyclable - Paper'];
                    @endphp


                    @if($level)
                        @foreach ($binTypes as $type)
                            @php
                                $bin = $level->bins->where('type', $type)->first();
                                $latestLevel = $bin?->wasteLevels->first();
                                $weight = $latestLevel?->weight ?? 0;
                                $fillLevel = $latestLevel?->level ?? 0;
                                $capacity = $bin?->capacity ?? 50;
                            @endphp

                            <div class="waste-cluster-card">
                                <div class="cluster-icon">
                                    <img src="{{ asset('img/icons/BinDark.png') }}" alt="Bin Icon">
                                    <h2>{{ $type }} ({{ $level->name }})</h2>
                                </div>
                                <div class="report-analytics">
                                    <table class="bin-status-table">
                                        <thead>
                                            <thead>
                                                <tr>
                                                    <th>Weight (Kg)</th>
                                                    <th>Fill Level (%)</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $weight }} Kg</td>
                                                <td>{{ $fillLevel }}%</td>
                                                <td>
                                                    @if($weight <= 0.1)
                                                        <span class="collected-status">Collected</span>
                                                    @else
                                                        <span class="in-progress-status">Active</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @if ($bin)
                                    <a href="{{ route('bin.statistics', ['id' => $bin->id]) }}" class="card-button">More info</a>
                                @else
                                    <button class="card-button" disabled>No Bin Data</button>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="Pick-Up-Schedule-Logs">
                <h2>College Rankings</h2>
                @if ($current && $current->is_active)
                    <div class="timeline">
                        @foreach($collegeRankings as $index => $college)
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <h5>{{ $index + 1 }}{{ $index == 0 ? 'st' : ($index == 1 ? 'nd' : ($index == 2 ? 'rd' : 'th')) }}
                                    </h5>
                                </div>
                                <div class="timeline-content">
                                    <h4>{{ $college->name }}</h4>
                                    <p>Points: {{ number_format($college->points, 0) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ranking-disabled" style="text-align:center; padding: 2rem;">
                        <p style="color: #aaa; font-size: 1.1rem;">
                            🕓 Semester ranking has not started or is currently stopped.
                        </p>
                    </div>
                @endif
            </div>
        </div>
        <hr class="divider2">
        <div class="waste-bin-report-chart">
            <div class="option-container-chart">
                <h2>Total Recycled Report</h2>
                <select id="waste-filter" class="form-select">
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="semester">Semester</option>
                </select>
            </div>
            <canvas id="wasteBinChart" height="120"></canvas>
        </div>

        <div class="prediction-container">
            <h2>Recommended Bin Collection</h2>

            <!-- Refresh Prediction -->
            <form action="{{ route('dashboard') }}" method="GET" style="margin-bottom: 1rem;">
                <button type="submit" class="btn btn-primary">
                    🔄 Refresh Prediction
                </button>
            </form>


            <!-- Success / Error Alerts -->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Prediction Table -->
            <table class="prediction-table">
                <thead>
                    <tr>
                        <th>Bin</th>
                        <th>Last Weight (Kg)</th>
                        <th>Fill Level (%)</th>
                        <th>Overflow Probability</th>
                        <th>Collection Needed</th>
                        <th>Recommended Collection Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($predictions as $pred)
                        <tr>
                            <td>{{ $pred['bin_type'] }}</td>
                            <td>{{ $pred['last_weight'] }}</td>
                            <td>{{ $pred['level_percent'] }}%</td>
                            <td>{{ $pred['overflow_probability'] }}%</td>
                            <td>{{ $pred['collection_needed'] ? 'Yes' : 'No' }}</td>
                            <td>{{ $pred['recommended_date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <script>
        // --- Utility: Safe fetch that handles offline ---
        function safeFetch(url, options = {}) {
            if (!navigator.onLine) {
                document.getElementById('offline-banner').style.display = 'block';
                return Promise.reject('Offline');
            }
            document.getElementById('offline-banner').style.display = 'none';
            return fetch(url, options);
        }

        // --- Listen to online/offline events ---
        window.addEventListener('offline', () => {
            document.getElementById('offline-banner').style.display = 'block';
        });
        window.addEventListener('online', () => {
            document.getElementById('offline-banner').style.display = 'none';
        });

        let wasteChart;
        const wasteBinCtx = document.getElementById('wasteBinChart').getContext('2d');
        const wasteFilter = document.getElementById('waste-filter');

        function loadWasteData(period = 'monthly') {
            safeFetch(`/api/waste-data?period=${period}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        console.warn('No waste data available for:', period);
                        return;
                    }

                    const labels = data.map(item => item.label);
                    const weights = data.map(item => item.total);

                    if (wasteChart) {
                        // Update existing chart
                        wasteChart.data.labels = labels;
                        wasteChart.data.datasets[0].data = weights;
                        wasteChart.update();
                    } else {
                        // Create chart for the first time
                        wasteChart = new Chart(wasteBinCtx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Total Waste (Kg)',
                                    data: weights,
                                    backgroundColor: 'rgba(40, 167, 69, 0.3)',
                                    borderColor: '#28a745',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: `Waste Production (${period.charAt(0).toUpperCase() + period.slice(1)})`
                                    },
                                    legend: { display: true, position: 'top' },
                                },
                                scales: {
                                    y: { beginAtZero: true, title: { display: true, text: 'Kilograms' } },
                                    x: { title: { display: true, text: 'Time Period' } }
                                }
                            }
                        });
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }

        // Load chart initially
        loadWasteData(wasteFilter.value);

        // Update chart when filter changes
        wasteFilter.addEventListener('change', e => {
            loadWasteData(e.target.value);
        });

        // Live update every 15 seconds
        setInterval(() => {
            loadWasteData(wasteFilter.value);
        }, 15000); // 15000 ms = 15 seconds

        document.addEventListener("DOMContentLoaded", () => {
            // Load default data
            loadWasteData('monthly');

            // Switch between weekly, monthly, semester
            wasteFilter.addEventListener('change', e => {
                const selectedPeriod = e.target.value;
                loadWasteData(selectedPeriod);
            });
        });

        // --- Dashboard KPIs & Cluster Cards ---
        const levelsData = @json($levels);

        function renderLevel(levelId) {
            const binContainer = document.getElementById("bin-container");
            const level = levelsData.find(l => l.id == levelId);
            const binTypes = ["Recyclable - Bottled Water", "Recyclable - Paper"];

            if (!level) {
                binContainer.innerHTML = `<p>No data available for this floor.</p>`;
                return;
            }

            binContainer.innerHTML = binTypes.map(type => {
                const bin = level.bins.find(b => b.type === type);
                const latestLevel = bin?.waste_levels?.[0];
                const weight = latestLevel?.weight ?? 0;
                const fillLevel = latestLevel?.level ?? 0;
                const binId = bin?.id ?? null;

                return `
                    <div class="waste-cluster-card">
                        <div class="cluster-icon">
                            <img src="{{ asset('img/icons/BinDark.png') }}" alt="Bin Icon">
                            <h2>${type} (${level.name})</h2>
                        </div>
                        <div class="report-analytics">
                            <table class="bin-status-table">
                                <thead><tr><th>Weight (Kg)</th><th>Fill Level (%)</th></tr></thead>
                                <tbody><tr><td>${weight}</td><td>${fillLevel}</td></tr></tbody>
                            </table>
                        </div>
                        ${binId ? `<a href="/bin-statistics/${binId}" class="card-button">More info</a>` : `<button class="card-button" disabled>No Bin Data</button>`}
                    </div>
                `;
            }).join("");
        }

        // --- Update dashboard live ---
        // --- Update dashboard live ---
        async function updateDashboard() {
            try {
                const res = await safeFetch('/api/dashboard-data');
                const data = await res.json();

                // Update KPI cards
                document.querySelector('.kpi-item1 h2').textContent = `${parseFloat(data.totalBottle).toFixed(2)} Kg`;
                document.querySelector('.kpi-item2 h2').textContent = `${parseFloat(data.totalPaper).toFixed(2)} Kg`;

                // Update bin cluster cards
                const binContainer = document.getElementById("bin-container");
                const levelsData = data.levels;
                const levelSelect = document.getElementById("bin-type");
                const level = levelsData.find(l => l.id == levelSelect.value);

                const binTypes = ["Recyclable - Bottled Water", "Recyclable - Paper"];
                if (!level) {
                    binContainer.innerHTML = `<p>No data available for this floor.</p>`;
                } else {
                    binContainer.innerHTML = binTypes.map(type => {
                        const bin = level.bins.find(b => b.type === type);
                        const latest = bin?.waste_levels?.[0];
                        const weight = latest?.weight ?? 0;
                        const fillLevel = latest?.level ?? 0;
                        const binId = bin?.id ?? null;

                        // Determine status
                        const status = (weight <= 0.1)
                            ? `<span class="collected-status">Collected</span>`
                            : `<span class="in-progress-status">Active</span>`;

                        return `
                        <div class="waste-cluster-card">
                            <div class="cluster-icon">
                                <img src="/img/icons/BinDark.png" alt="Bin Icon">
                                <h2>${type} (${level.name})</h2>
                            </div>
                            <div class="report-analytics">
                                <table class="bin-status-table">
                                    <thead>
                                        <tr>
                                            <th>Weight (Kg)</th>
                                            <th>Fill Level (%)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>${weight.toFixed(2)}</td>
                                            <td>${fillLevel}</td>
                                            <td>${status}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            ${binId ? `<a href="/bin-statistics/${binId}" class="card-button">More info</a>` : `<button class="card-button" disabled>No Bin Data</button>`}
                        </div>`;
                    }).join('');
                }

                // Update prediction table
                const predictionBody = document.querySelector('.prediction-table tbody');
                predictionBody.innerHTML = data.predictions.map(p => `
                <tr>
                    <td>${p.bin_type}</td>
                    <td>${p.last_weight}</td>
                    <td>${p.level_percent}%</td>
                    <td>${p.overflow_probability}%</td>
                    <td>${p.collection_needed ? 'Yes' : 'No'}</td>
                    <td>${p.recommended_date}</td>
                </tr>
            `).join('');
            } catch (err) {
                console.error('Dashboard fetch error:', err);
            }
        }

        // --- DOM ready & live polling ---
        document.addEventListener("DOMContentLoaded", () => {
            const levelSelect = document.getElementById("bin-type");

            // Initial render
            updateDashboard();

            // Update when floor selection changes
            levelSelect.addEventListener("change", updateDashboard);

            // Live update every 10 seconds
            setInterval(updateDashboard, 10000);
        });


    </script>


@endsection