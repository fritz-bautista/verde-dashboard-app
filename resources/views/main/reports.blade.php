@extends('nav_bar')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/reports.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-dashboard-container {
            padding: 20px;
        }

        .reports-container {
            padding: 20px;
        }

        .report-header h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .parent {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            grid-template-rows: repeat(9, 1fr);
            gap: 19px;
        }

        .chart-box {
            background: #f9fafb;
            border-radius: 19px;
            border: 2px solid #c3c6ce;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .chart-box h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #333;
        }

        .chart-box canvas {
            width: 100% !important;
            height: 250px !important;
        }

        /* Layouts */
        .div1 {
            grid-column: span 3 / span 3;
            grid-row: span 3 / span 3;
        }

        .div2 {
            grid-column: span 2 / span 2;
            grid-row: span 3 / span 3;
            grid-column-start: 4;
        }

        .div4 {
            grid-column: span 3 / span 3;
            grid-row: span 3 / span 3;
            grid-row-start: 4;
        }

        .div5 {
            grid-column: span 2 / span 2;
            grid-row: span 3 / span 3;
            grid-column-start: 4;
            grid-row-start: 4;
        }

        .div6 {
            grid-column: span 3 / span 3;
            grid-row: span 3 / span 3;
            grid-row-start: 7;
        }

        .div7 {
            grid-column: span 2 / span 2;
            grid-row: span 3 / span 3;
            grid-column-start: 4;
            grid-row-start: 7;
        }

        /* Report Generator Boxes */
        .report-generator {
            background: #ffffff;
            border-radius: 19px;
            border: 2px solid #c3c6ce;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: left;
        }

        .report-generator h3 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .report-generator label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }

        .report-generator select,
        .report-generator input {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .report-generator button {
            width: 48%;
            padding: 8px;
            border: none;
            border-radius: 6px;
            background-color: #2563eb;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }

        .report-generator button:nth-child(2) {
            background-color: #10b981;
        }

        .report-generator button:hover {
            opacity: 0.9;
        }

        @media (max-width: 900px) {
            .parent {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            .chart-box,
            .report-generator {
                height: auto;
            }
        }
    </style>
@endsection

@section('admin-title', 'Report Analytics')

@section('admin-content')
    <div class="report-dashboard-container">
        <div class="reports-container">
            <div class="parent">
                <!-- College Chart -->
                <div class="chart-box div1">
                    <h3>College with Most Recycled Waste</h3>
                    <canvas id="collegeChart"></canvas>
                </div>
                <div class="report-generator div2">
                    <h3>College Report Generator</h3>
                    <form id="collegeReportForm">
                        <label for="collegeFilter">Select College</label>
                        <select id="collegeFilter" name="collegeFilter">
                            <option value="">All Colleges</option>
                            @foreach ($recycleData as $item)
                                <option value="{{ $item->college_name }}">{{ $item->college_name }}</option>
                            @endforeach
                        </select>


                        <label for="dateRangeCollege">Date Range</label>
                        <input type="date" id="collegeStartDate"> to
                        <input type="date" id="collegeEndDate">

                        <div style="display:flex; justify-content:space-between;">
                            <button type="button" onclick="generateReport('college', 'pdf')">Download PDF</button>
                            <button type="button" onclick="generateReport('college', 'excel')">Download Excel</button>
                        </div>
                    </form>
                </div>

                <!-- Floor Chart -->
                <div class="chart-box div4">
                    <h3>Floor with Most Waste</h3>
                    <canvas id="floorChart"></canvas>
                </div>
                <div class="report-generator div5">
                    <h3>Floor Report Generator</h3>
                    <form id="floorReportForm">
                        <label for="floorFilter">Select Floor</label>
                        <select id="floorFilter" name="floorFilter">
                            <option value="">All Floors</option>
                            @foreach ($floorData as $floor)
                                <option value="{{ $floor['floor'] }}">{{ $floor['floor'] }}</option>
                            @endforeach
                        </select>

                        <label for="dateRangeFloor">Date Range</label>
                        <input type="date" id="floorStartDate"> to
                        <input type="date" id="floorEndDate">

                        <div style="display:flex; justify-content:space-between;">
                            <button type="button" onclick="generateReport('floor', 'pdf')">Download PDF</button>
                            <button type="button" onclick="generateReport('floor', 'excel')">Download Excel</button>
                        </div>
                    </form>
                </div>

                <!-- Bin Chart -->
                <div class="chart-box div6">
                    <h3>Bin Producing Most Waste</h3>
                    <canvas id="binChart"></canvas>
                </div>
                <div class="report-generator div7">
                    <h3>Bin Report Generator</h3>
                    <form id="binReportForm">
                        <label for="binFilter">Select Bin</label>
                        <select id="binFilter" name="binFilter">
                            <option value="">All Bins</option>
                            @foreach ($binData as $bin)
                                <option value="{{ $bin['bin'] }}">{{ $bin['bin'] }}</option>
                            @endforeach
                        </select>

                        <label for="dateRangeBin">Date Range</label>
                        <input type="date" id="binStartDate"> to
                        <input type="date" id="binEndDate">

                        <div style="display:flex; justify-content:space-between;">
                            <button type="button" onclick="generateReport('bin', 'pdf')">Download PDF</button>
                            <button type="button" onclick="generateReport('bin', 'excel')">Download Excel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const recycleData = @json($recycleData);
            const floorData = @json($floorData);
            const binData = @json($binData);

            // College Chart
            // College Chart
            new Chart(document.getElementById('collegeChart'), {
                type: 'bar',
                data: {
                    labels: recycleData.map(c => c.college_name),
                    datasets: [{
                        label: 'Total Recycled Waste (kg)',
                        data: recycleData.map(c => c.total_weight),
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderWidth: 1
                    }]
                }
            });


            // Floor Chart
            new Chart(document.getElementById('floorChart'), {
                type: 'bar',
                data: {
                    labels: floorData.map(f => f.floor),
                    datasets: [{
                        label: 'Total Waste (kg)',
                        data: floorData.map(f => f.total_weight),
                        backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        borderWidth: 1
                    }]
                }
            });

            // Bin Chart
            new Chart(document.getElementById('binChart'), {
                type: 'bar',
                data: {
                    labels: binData.map(b => b.bin),
                    datasets: [{
                        label: 'Total Waste (kg)',
                        data: binData.map(b => b.total_weight),
                        backgroundColor: 'rgba(153, 102, 255, 0.7)',
                        borderWidth: 1
                    }]
                }
            });
        });

        // Mock download generator
        function generateReport(type, format) {
            const url = `/reports/download/${type}/${format}`;
            window.location.href = url; // triggers download
        }

    </script>
@endsection