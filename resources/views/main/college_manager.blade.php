@extends('nav_bar')

@section('admin-title', 'College Manager')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/college-manager.css') }}">
@endsection

@section('admin-content')
<div class="college-container">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <div class="college-grid">
        <!-- College CRUD -->
        <div class="college-crud">
            <div class="header">
                <h3>College List</h3>
                <button class="btn add" onclick="document.getElementById('addModal').showModal()">Add</button>
            </div>

            <table class="college-table">
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
                            <td>{{ $college->student_count ?? 0 }}</td>
                            <td>
                                <form action="{{ route('college.destroy', $college->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this college?')" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn delete">Delete</button>
                                </form>
                                <button class="btn edit"
                                    onclick="editCollege('{{ $college->id }}', '{{ $college->name }}', '{{ $college->dean }}')">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No colleges added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- College Ranking / Analytics -->
        <div class="college-analytics">
            <h3>College Ranking</h3>
            <table class="ranking-table">
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
    </div>

    <!-- Add College Modal -->
    <dialog id="addModal" class="modal">
        <form method="POST" action="{{ route('college.store') }}" class="modal-form">
            @csrf
            <h3>Add New College</h3>
            <label>Name</label>
            <input type="text" name="name" required>
            <label>Dean</label>
            <input type="text" name="dean">
            <label>Student Count</label>
            <input type="number" name="student_count" min="0">
            <div class="modal-buttons">
                <button type="button" class="btn cancel" onclick="document.getElementById('addModal').close()">Cancel</button>
                <button class="btn save">Save</button>
            </div>
        </form>
    </dialog>

    <!-- Edit Modal -->
    <dialog id="editModal" class="modal">
        <form id="editForm" method="POST" class="modal-form">
            @csrf
            @method('PUT')
            <h3>Edit College</h3>
            <label>Name</label>
            <input type="text" name="name" id="editName" required>
            <label>Dean</label>
            <input type="text" name="dean" id="editDean">
            <div class="modal-buttons">
                <button type="button" class="btn cancel" onclick="document.getElementById('editModal').close()">Cancel</button>
                <button class="btn save">Update</button>
            </div>
        </form>
    </dialog>
</div>

<script>
function editCollege(id, name, dean) {
    document.getElementById('editModal').showModal();
    document.getElementById('editName').value = name;
    document.getElementById('editDean').value = dean;
    document.getElementById('editForm').action = `/college/${id}`;
}
</script>
@endsection
