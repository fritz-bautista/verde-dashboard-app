@extends('nav_bar')

@section('admin-title', 'Semester Ranking Manager')

@section('admin-content')
    <div class="ranking-container">
        @if ($current)
            <div class="current-semester">
                <h4>Current Semester: <b>{{ $current->semester_name }}</b></h4>
                <h5>Status:
                    <span id="rankingStatus" class="badge {{ $current->is_active ? 'active' : 'inactive' }}">
                        {{ $current->status }}
                    </span>
                </h5>
                <p class="started-at">
                    Started: {{ $current->started_at ? $current->started_at->format('M d, Y h:i A') : 'N/A' }}
                </p>
            </div>

            <div class="action-buttons">
                @if (!$current->is_active)
                    <button id="startRankingBtn" class="btn btn-success">Start New Semester</button>
                @else
                    <button id="stopRankingBtn" class="btn btn-danger">Stop Current Semester</button>
                @endif
            </div>
        @else
            <div class="no-semester">
                <p>No semester ranking is currently active.</p>
                <button id="startRankingBtn" class="btn btn-success">Start First Semester</button>
            </div>
        @endif
    </div>

    <!-- History Section -->
    <div class="ranking-history mt-4">
        <h4>Ranking History</h4>
        <hr class="divider">
        <div class="table-container">
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Started At</th>
                        <th>Stopped At</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $rank)
                        <tr>
                            <td>{{ $rank->id }}</td>
                            <td>{{ $rank->semester_name }}</td>
                            <td>
                                <span class="badge {{ $rank->is_active ? 'active' : 'inactive' }}">
                                    {{ $rank->status }}
                                </span>
                            </td>
                            <td>{{ $rank->started_at ? $rank->started_at->format('M d, Y h:i A') : '—' }}</td>
                            <td>{{ $rank->stopped_at ? $rank->stopped_at->format('M d, Y h:i A') : '—' }}</td>
                            <td>{{ $rank->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No ranking history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Start New Semester Modal -->
    <div id="startModal" class="modal">
        <div class="modal-content">
            <h5>Start New Semester Ranking</h5>
            <label>Semester Name:</label>
            <input type="text" id="semesterName" class="input-field" placeholder="e.g. 2nd Semester 2025-2026">

            <label>Start Date:</label>
            <input type="datetime-local" id="semesterStart" class="input-field">

            <label>Stop Date:</label>
            <input type="datetime-local" id="semesterStop" class="input-field">

            <div class="modal-actions">
                <button class="btn btn-secondary" id="cancelStart">Cancel</button>
                <button class="btn btn-success" id="confirmStartBtn">Start Semester</button>
            </div>
        </div>
    </div>

    <!-- Stop Confirmation Modal -->
    <div id="stopModal" class="modal">
        <div class="modal-content">
            <h5>Confirm Stop Ranking</h5>
            <p>Are you sure you want to stop the semester ranking? This will finalize the semester.</p>
            <label>Stop Date:</label>
            <input type="datetime-local" id="stopDate" class="input-field">

            <div class="modal-actions">
                <button class="btn btn-secondary" id="cancelStop">Cancel</button>
                <button class="btn btn-danger" id="confirmStopBtn">Stop Ranking</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startBtn = document.getElementById('startRankingBtn');
            const stopBtn = document.getElementById('stopRankingBtn');
            const confirmStopBtn = document.getElementById('confirmStopBtn');
            const confirmStartBtn = document.getElementById('confirmStartBtn');

            const startModal = document.getElementById('startModal');
            const stopModal = document.getElementById('stopModal');
            const cancelStart = document.getElementById('cancelStart');
            const cancelStop = document.getElementById('cancelStop');

            function showModal(modal) { modal.style.display = 'flex'; }
            function hideModal(modal) { modal.style.display = 'none'; }

            if (startBtn) startBtn.addEventListener('click', () => showModal(startModal));
            if (stopBtn) stopBtn.addEventListener('click', () => showModal(stopModal));
            if (cancelStart) cancelStart.addEventListener('click', () => hideModal(startModal));
            if (cancelStop) cancelStop.addEventListener('click', () => hideModal(stopModal));

            // Start semester
            if (confirmStartBtn) {
                confirmStartBtn.addEventListener('click', async () => {
                    const semesterName = document.getElementById('semesterName').value.trim() || 'Unnamed Semester';
                    const startDate = document.getElementById('semesterStart').value;
                    const stopDate = document.getElementById('semesterStop').value;

                    const res = await fetch('{{ route('ranking.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'start',
                            semester_name: semesterName,
                            started_at: startDate || new Date().toISOString(),
                            stopped_at: stopDate || null
                        })
                    });

                    const data = await res.json();
                    if (data.success) location.reload();
                });
            }

            // Stop semester
            if (confirmStopBtn) {
                confirmStopBtn.addEventListener('click', async () => {
                    const stopDate = document.getElementById('stopDate').value || new Date().toISOString();
                    const res = await fetch('{{ route('ranking.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ action: 'stop', stopped_at: stopDate })
                    });

                    const data = await res.json();
                    if (data.success) location.reload();
                });
            }
        });

    </script>

    <style>
        /* Container */
        .ranking-container,
        .ranking-history {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .current-semester h4 {
            font-size: 24px;
            font-weight: 600;
            color: #2e2e2e;
        }

        .current-semester h5 {
            font-size: 20px;
            color: #4b4b4b;
        }

        .started-at {
            font-size: 16px;
            color: #6f6f6f;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .no-semester {
            padding: 15px;
            font-size: 18px;
            color: #444;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        .ranking-table th {
            background: #0B7D3F;
            color: #fff;
            text-align: left;
            padding: 10px;
            font-weight: 500;
        }

        .ranking-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }

        .ranking-table tr:hover {
            background: #f1f1f1;
            transition: 0.3s;
        }

        .badge {
            font-size: 14px;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .badge.active {
            background: #0B7D3F;
            color: #fff;
        }

        .badge.inactive {
            background: #6c757d;
            color: #fff;
        }

        /* Divider */
        .divider {
            border: none;
            height: 3px;
            width: 100%;
            background: linear-gradient(to right, #0B7D3F, #188148);
            border-radius: 5px;
            margin: 15px 0;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-success {
            background: #0B7D3F;
            color: #fff;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            text-align: left;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-content h5 {
            margin-top: 0;
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }

        .input-field {
            width: 100%;
            padding: 8px 10px;
            font-size: 16px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
    </style>
@endpush