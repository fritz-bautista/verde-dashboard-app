@extends('nav_bar')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/bin_manager.css') }}">
@endsection

@section('admin-title', 'Bin Manager')

@section('admin-content')
<div class="dashboard-container">
    <div class="waste-reports">
        <h1>Waste Bin Status</h1>
        <div class="filter-options">
            <label for="bin-floor">Floor:</label>
            <select id="bin-floor">
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                @endforeach
            </select>
            <button id="add-bin-btn" class="btn btn-success">➕ Add Bin</button>
        </div>

        <div id="waste-cluster-cards">
            <p>Loading...</p>
        </div>
    </div>
</div>

<!-- Add Bin Modal -->
<div id="add-bin-modal" style="display:none;">
    <form id="add-bin-form">
        <h3>Add New Bin</h3>
        <label>Bin Type:</label>
        <input type="text" name="type" required>
        <label>Capacity (Kg):</label>
        <input type="number" name="capacity" value="50" min="1" required>
        <input type="hidden" name="level_id" id="modal-level-id">
        <button type="submit" class="btn btn-primary">Add Bin</button>
        <button type="button" id="close-modal" class="btn btn-secondary">Cancel</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('waste-cluster-cards');
    const floorDropdown = document.getElementById('bin-floor');
    const addBinBtn = document.getElementById('add-bin-btn');
    const addModal = document.getElementById('add-bin-modal');
    const closeModalBtn = document.getElementById('close-modal');
    const modalLevelId = document.getElementById('modal-level-id');

    let isFetching = false;

    function fetchBins(levelId) {
        if (isFetching) return;
        isFetching = true;
        container.innerHTML = '<p>Loading...</p>';

        fetch(`/api/bin-status?floor=${levelId}`)
            .then(res => res.ok ? res.json() : Promise.reject('Network error'))
            .then(data => {
                container.innerHTML = '';
                modalLevelId.value = levelId;

                if (!data.bins.length) {
                    container.innerHTML = '<p>No bins available for this floor.</p>';
                    return;
                }

                data.bins.forEach(bin => {
                    const card = document.createElement('div');
                    card.className = 'waste-cluster-card';
                    card.innerHTML = `
                        <div class="cluster-icon">
                            <h2>${bin.type} (${data.levelName})</h2>
                        </div>
                        <div class="report-analytics">
                            <table class="bin-status-table">
                                <thead><tr><th>Weight (Kg)</th><th>Fill Level (%)</th></tr></thead>
                                <tbody><tr><td>${bin.weight}</td><td>${bin.percent}</td></tr></tbody>
                            </table>
                        </div>
                        <button class="btn btn-danger delete-bin-btn" data-id="${bin.binId}">🗑️ Delete</button>
                    `;
                    container.appendChild(card);
                });

                document.querySelectorAll('.delete-bin-btn').forEach(btn => {
                    btn.addEventListener('click', handleDeleteBin);
                });
            })
            .catch(err => {
                console.error('Fetch error:', err);
                container.innerHTML = '<p>Error loading bin data.</p>';
            })
            .finally(() => { isFetching = false; });
    }

    function handleDeleteBin(e) {
        const binId = e.target.dataset.id;
        const confirmDelete = prompt('Type DELETE to confirm deletion of this bin and all its data:');
        if (confirmDelete !== 'DELETE') return alert('Deletion cancelled.');

        fetch(`/api/bin/${binId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }})
            .then(res => res.ok ? res.json() : Promise.reject('Delete failed'))
            .then(data => {
                alert(data.message);
                fetchBins(floorDropdown.value);
            })
            .catch(err => console.error(err));
    }

    floorDropdown.addEventListener('change', () => fetchBins(floorDropdown.value));

    // Add bin modal
    addBinBtn.addEventListener('click', () => addModal.style.display = 'block');
    closeModalBtn.addEventListener('click', () => addModal.style.display = 'none');

    document.getElementById('add-bin-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/api/bin', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.ok ? res.json() : Promise.reject('Add failed'))
        .then(data => {
            alert(data.message);
            addModal.style.display = 'none';
            fetchBins(floorDropdown.value);
        })
        .catch(err => console.error(err));
    });

    // Initial load
    fetchBins(floorDropdown.value);
});
</script>
@endpush
