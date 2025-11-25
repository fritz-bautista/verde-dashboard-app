@extends('nav_bar')

@section('admin-title', 'College & Ranking Manager')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/college-ranking.css') }}">
@endsection

@section('admin-content')
    <div class="manager-container">
        <div class="section college-section">
            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            <div class="section-header">
                <h3>College Manager</h3>
                <button class="btn add" onclick="showModal('addCollegeModal')">Add College</button>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Dean</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                        <tr>
                            <td>{{ $college->name }}</td>
                            <td>{{ $college->dean ?? 'N/A' }}</td>
                            <td>{{ $college->students_count ?? 0 }}</td>
                            <td>
                                <button class="btn edit"
                                    onclick="editCollege('{{ $college->id }}', '{{ $college->name }}', '{{ $college->dean }}', '{{ $college->student_count ?? 0 }}')">
                                    Edit
                                </button>
                                <form action="{{ route('college.destroy', $college->id) }}" method="POST"
                                    style="display:inline-block;" onsubmit="return confirm('Delete this college?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No colleges added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h4 class="subheading">College Rankings</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>College</th>
                        <th>Cleanliness Score</th>
                        <th>Waste Managed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rankings as $index => $rank)
                        <tr>
                            <td>#{{ $index + 1 }}</td>
                            <td>{{ $rank->college->name }}</td>
                            <td>{{ $rank->score }}</td>
                            <td>{{ $rank->waste_managed }} kg</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No ranking data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ===========================
                    Semester Ranking Section
                    ============================ -->
        <div class="section ranking-section">
            <h3>Semester Ranking Manager</h3>
            <hr class="divider">

            @if($current)
                <div class="semester-info">
                    <div class="current-semester">
                        <div class="cont">
                            <h4>Current Semester: </h4>
                            <b>{{ $current->semester_name }}</b>
                        </div>
                        <p>Status: <span
                                class="badge {{ $current->is_active ? 'active' : 'inactive' }}">{{ $current->status }}</span>
                        </p>
                        <p>Started at: {{ $current->started_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                        <p>Stopped at: {{ $current->stopped_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>

                    <div class="action-buttons">
                        @if($current->is_active)
                            <button class="btn stop" onclick="showModal('stopModal')">Stop Semester</button>
                        @else
                            <button class="btn start" onclick="showModal('startModal')">Start New Semester</button>
                        @endif
                    </div>
                </div>
            @else
                <div class="no-semester">
                    <p>No semester ranking is currently active.</p>
                    <button class="btn start" onclick="showModal('startModal')">Start First Semester</button>
                </div>
            @endif

            <!-- Ranking History -->
            <div class="ranking-history mt-4">
                <hr class="divider">
                <h4>Ranking History</h4>
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
                            @forelse($history as $rank)
                                <tr class="history-row" data-ranking-id="{{ $rank->id }}" style="cursor:pointer;">
                                    <td>{{ $rank->id }}</td>
                                    <td>{{ $rank->semester_name }}</td>
                                    <td><span
                                            class="badge {{ $rank->is_active ? 'active' : 'inactive' }}">{{ $rank->status }}</span>
                                    </td>
                                    <td>{{ $rank->started_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td>{{ $rank->stopped_at?->format('M d, Y h:i A') ?? '—' }}</td>
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

            <!-- Semester Detail Modal -->
            <dialog id="semesterDetailModal" class="modal">
                <div class="modal-content">
                    <h5>Semester Points</h5>
                    <div id="semesterPoints"></div>
                    <div class="modal-actions">
                        <a id="downloadPdfBtn" href="#" class="btn btn-success" target="_blank">Download PDF</a>
                        <button class="btn btn-secondary" id="closeSemesterModal">Close</button>
                    </div>
                </div>
            </dialog>

        </div>

    </div>

    <!-- ===========================
                                             Modals: Add/Edit/Start/Stop
                                        ============================ -->

    <!-- Add College Modal -->
    <dialog id="addCollegeModal" class="modal">
        <form action="{{ route('college.store') }}" method="POST" class="modal-form">
            @csrf
            <h4>Add College</h4>
            <label>Name</label>
            <input type="text" name="name" required>
            <label>Dean</label>
            <input type="text" name="dean">
            <label>Student Count</label>
            <input type="number" name="student_count" min="0">
            <div class="modal-buttons">
                <button type="button" class="btn cancel" onclick="closeModal('addCollegeModal')">Cancel</button>
                <button class="btn save">Save</button>
            </div>
        </form>
    </dialog>

    <!-- Edit College Modal -->
    <dialog id="editCollegeModal" class="modal">
        <form id="editCollegeForm" method="POST" class="modal-form">
            @csrf
            @method('PUT')
            <h4>Edit College</h4>
            <label>Name</label>
            <input type="text" name="name" id="editCollegeName" required>
            <label>Dean</label>
            <input type="text" name="dean" id="editCollegeDean">
            <label>Student Count</label>
            <input type="number" name="student_count" id="editCollegeCount" min="0">
            <div class="modal-buttons">
                <button type="button" class="btn cancel" onclick="closeModal('editCollegeModal')">Cancel</button>
                <button class="btn save">Update</button>
            </div>
        </form>
    </dialog>

    <!-- Start Semester Modal -->
    <dialog id="startModal" class="modal">
        <div class="modal-content">
            <h4>Start New Semester</h4>
            <label>Semester Name:</label>
            <input type="text" id="semesterName" placeholder="e.g. 2nd Semester 2025-2026" required>
            <label>Start Date & Time:</label>
            <input type="datetime-local" id="semesterStart" required>
            <div class="modal-buttons">
                <button class="btn cancel" onclick="closeModal('startModal')">Cancel</button>
                <button class="btn start" id="confirmStartBtn">Start Semester</button>
            </div>
        </div>
    </dialog>

    <!-- Stop Semester Modal -->
    <dialog id="stopModal" class="modal">
        <div class="modal-content">
            <h4>Stop Current Semester</h4>
            <label>Stop Date & Time:</label>
            <input type="datetime-local" id="semesterStop" required>
            <p>Are you sure you want to stop the current semester ranking?</p>
            <div class="modal-buttons">
                <button class="btn cancel" onclick="closeModal('stopModal')">Cancel</button>
                <button class="btn stop" id="confirmStopBtn">Stop Semester</button>
            </div>
        </div>
    </dialog>

@endsection

@push('scripts')
    <script>
        function showModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        function editCollege(id, name, dean, count) {
            showModal('editCollegeModal');
            document.getElementById('editCollegeName').value = name;
            document.getElementById('editCollegeDean').value = dean;
            document.getElementById('editCollegeCount').value = count;
            document.getElementById('editCollegeForm').action = `/college/${id}`;
        }

        document.addEventListener('DOMContentLoaded', function () {

            // Start/Stop semester
            const confirmStart = document.getElementById('confirmStartBtn');
            const confirmStop = document.getElementById('confirmStopBtn');
            const semesterStart = document.getElementById('semesterStart');
            const semesterStop = document.getElementById('semesterStop');
            const semesterName = document.getElementById('semesterName');

            if (confirmStart) {
                confirmStart.addEventListener('click', async () => {
                    const res = await fetch('{{ route('ranking.toggle') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'start', semester_name: semesterName.value, started_at: semesterStart.value })
                    });
                    const data = await res.json();
                    if (data.success) location.reload();
                });
            }

            if (confirmStop) {
                confirmStop.addEventListener('click', async () => {
                    const res = await fetch('{{ route('ranking.toggle') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'stop', stopped_at: semesterStop.value })
                    });
                    const data = await res.json();
                    if (data.success) location.reload();
                });
            }

            // Ranking History Modal
            const modal = document.getElementById('semesterDetailModal');
            const pointsContainer = document.getElementById('semesterPoints');
            const downloadBtn = document.getElementById('downloadPdfBtn');
            const closeModalBtn = document.getElementById('closeSemesterModal');

            document.querySelectorAll('.history-row').forEach(row => {
                row.addEventListener('click', async () => {
                    const rankingId = row.dataset.rankingId;
                    const res = await fetch(`/admin/ranking/${rankingId}/points`);
                    const data = await res.json();
                    if (data.success) {
                        let html = '<table class="table"><thead><tr><th>College</th><th>Score</th></tr></thead><tbody>';
                        data.points.forEach(p => {
                            html += `<tr><td>${p.college_name}</td><td>${p.score}</td></tr>`;
                        });
                        html += '</tbody></table>';
                        pointsContainer.innerHTML = html;
                        downloadBtn.href = `/ranking/${rankingId}/pdf`;
                        modal.showModal();
                    } else {
                        pointsContainer.innerHTML = '<p>No points data found.</p>';
                    }
                });
            });

            if (closeModalBtn) closeModalBtn.addEventListener('click', () => modal.close());
        });
    </script>
@endpush