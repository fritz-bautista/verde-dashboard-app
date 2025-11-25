@extends('nav_bar')

@section('admin-title', 'Settings — Waste Levels Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">

@endsection

@section('admin-content')
<div class="settings-wrap">
    <h2>Waste Levels — Settings</h2>
    <p class="notice">Preview and delete waste level records for a chosen bin and date. Deleting requires typing <strong>DELETE</strong> in the confirmation box.</p>

    <div class="controls">
        <div class="form-group">
            <label for="binSelect">Select Bin</label>
            <select id="binSelect">
                <option value="">-- choose bin --</option>
                @foreach($bins as $b)
                    <option value="{{ $b->id }}">{{ $b->type }} (id: {{ $b->id }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="dateFrom">Date from (optional)</label>
            <input type="date" id="dateFrom">
        </div>

        <div class="form-group">
            <label for="limit">Rows to show</label>
            <input type="number" id="limit" min="1" max="1000" value="200">
        </div>

        <div class="form-group">
            <button id="loadBtn" class="btn-primary" style="margin-top:1.6rem;">Load Preview</button>
        </div>
    </div>

    <div id="previewContainer" class="table-preview" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
            <div><strong>Preview</strong> — <span id="previewCount">0</span> rows</div>
            <div>
                <button id="refreshBtn" class="btn-primary" style="margin-right:0.5rem;">Refresh</button>
            </div>
        </div>

        <table id="previewTable" class="preview-table">
            <thead><tr id="previewHead"></tr></thead>
            <tbody id="previewBody"></tbody>
        </table>
    </div>

    <hr style="margin:1rem 0;">

    <h4>Delete records</h4>
    <p class="notice">This action is destructive. You will delete all previewed rows (bin + date filter). Type <strong>DELETE</strong> to confirm.</p>

    <form id="deleteForm" method="POST" action="{{ route('settings.deleteRecords') }}">
        @csrf
        <input type="hidden" name="bin_id" id="delete_bin_id">
        <input type="hidden" name="date_from" id="delete_date_from">

        <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <label>Confirmation:
                <input type="text" name="confirmation" id="confirmation" class="confirm-input" placeholder="Type DELETE to confirm" required>
            </label>

            <button type="submit" id="deleteBtn" class="btn-danger-block" disabled>Delete Filtered Records</button>
        </div>
    </form>

    <div id="flashMessage" style="margin-top:1rem;"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const binSelect = document.getElementById('binSelect');
    const dateFrom = document.getElementById('dateFrom');
    const limitElm = document.getElementById('limit');
    const loadBtn = document.getElementById('loadBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const previewContainer = document.getElementById('previewContainer');
    const previewHead = document.getElementById('previewHead');
    const previewBody = document.getElementById('previewBody');
    const previewCount = document.getElementById('previewCount');
    const deleteForm = document.getElementById('deleteForm');
    const deleteBinId = document.getElementById('delete_bin_id');
    const deleteDateFrom = document.getElementById('delete_date_from');
    const confirmation = document.getElementById('confirmation');
    const deleteBtn = document.getElementById('deleteBtn');
    const flashMessage = document.getElementById('flashMessage');

    function showFlash(type, msg) {
        flashMessage.innerHTML = `<div style="padding:0.6rem; border-radius:6px; ${type==='error' ? 'background:#ffe6e6;border:1px solid #ffb3b3;' : 'background:#e6ffed;border:1px solid #b3ffca;'}">${msg}</div>`;
        setTimeout(()=> flashMessage.innerHTML = '', 5000);
    }

    async function loadPreview() {
        const bin_id = binSelect.value;
        if (!bin_id) {
            showFlash('error', 'Please select a bin first.');
            return;
        }

        const params = new URLSearchParams({
            bin_id,
            date_from: dateFrom.value || '',
            limit: limitElm.value || 200
        });

        try {
            const res = await fetch(`{{ route('settings.loadTable') }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data.success) {
                const err = data.errors ? JSON.stringify(data.errors) : (data.message || 'Failed to load');
                showFlash('error', err);
                return;
            }

            // render table
            previewHead.innerHTML = '';
            previewBody.innerHTML = '';

            data.columns.forEach(col => {
                const th = document.createElement('th');
                th.textContent = col;
                previewHead.appendChild(th);
            });

            data.rows.forEach(row => {
                const tr = document.createElement('tr');
                data.columns.forEach(col => {
                    const td = document.createElement('td');
                    let val = row[col];
                    if (val === null || val === undefined) val = '';
                    td.textContent = val;
                    tr.appendChild(td);
                });
                previewBody.appendChild(tr);
            });

            previewCount.textContent = data.count;
            previewContainer.style.display = 'block';

            // set delete hidden inputs
            deleteBinId.value = bin_id;
            deleteDateFrom.value = dateFrom.value || '';

            // enable delete if there are rows
            deleteBtn.disabled = data.count === 0;

        } catch (err) {
            console.error(err);
            showFlash('error', 'Network error while loading preview.');
        }
    }

    loadBtn.addEventListener('click', loadPreview);
    refreshBtn.addEventListener('click', loadPreview);

    // enable delete button only when confirmation equals 'DELETE'
    confirmation.addEventListener('input', function () {
        deleteBtn.disabled = (this.value !== 'DELETE');
    });

    // Optional: intercept delete to show a confirm dialog before submitting
    deleteForm.addEventListener('submit', function (e) {
        if (confirmation.value !== 'DELETE') {
            e.preventDefault();
            showFlash('error', 'You must type DELETE in the confirmation box.');
            return;
        }

        // final confirm
        const ok = confirm('This will permanently delete the filtered waste level records. Proceed?');
        if (!ok) e.preventDefault();
    });

    // if page loads with a preselected bin, auto-load preview
    if (binSelect.value) loadPreview();
});
</script>
@endpush
