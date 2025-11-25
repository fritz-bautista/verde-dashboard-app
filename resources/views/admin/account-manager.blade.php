@extends('nav_bar')

@section('admin-title', 'Account Manager')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/account-manager-admin.css') }}">

@endsection

@section('admin-content')
    <div class="account-manager">
        <div class="top-actions">
            <button id="addAccountBtn" class="btn btn-primary">Add New Account</button>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="roleTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins"
                    type="button">Admins</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="colleges-tab" data-bs-toggle="tab" data-bs-target="#colleges"
                    type="button">Colleges</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students"
                    type="button">Students</button>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Admins -->
            <div class="tab-pane fade show active" id="admins">
                @include('admin.partials.account-table', ['users' => $admins, 'role' => 'admin'])
            </div>

            <!-- Colleges -->
            <div class="tab-pane fade" id="colleges">
                @include('admin.partials.account-table', ['users' => $colleges, 'role' => 'college'])
            </div>

            <!-- Students -->
            <div class="tab-pane fade" id="students">
                @include('admin.partials.account-table', ['users' => $students, 'role' => 'student'])
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="accountModal" class="modal">
        <h5 id="modalTitle">Add/Edit Account</h5>
        <form id="accountForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="user_id" id="accountUserId">

            <div class="mb-3">
                <label>Name</label>
                <input type="text" id="accountName" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" id="accountEmail" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select id="accountRole" name="role" class="form-control" required>
                    <option value="admin">Admin</option>
                    <option value="college">College</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <!-- College Selector (shown only for students) -->
            <div class="mb-3" id="collegeSelector" style="display:none;">
                <label>College</label>
                <select id="accountCollege" name="college_id" class="form-control">
                    <option value="">-- Select College --</option>
                    @foreach(App\Models\College::all() as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Password (optional)</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password">
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" id="closeAccountModal" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountRole = document.getElementById('accountRole');
            const collegeSelector = document.getElementById('collegeSelector');

            function toggleCollegeSelector() {
                if (accountRole.value === 'student') {
                    collegeSelector.style.display = 'block';
                } else {
                    collegeSelector.style.display = 'none';
                    document.getElementById('accountCollege').value = '';
                }
            }

            // initial check
            toggleCollegeSelector();

            // listen to role changes
            accountRole.addEventListener('change', toggleCollegeSelector);
        });
    </script>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('accountModal');
            const closeModal = document.getElementById('closeAccountModal');
            const form = document.getElementById('accountForm');
            const modalTitle = document.getElementById('modalTitle');
            const formMethod = document.getElementById('formMethod');

            // Open modal for adding
            document.getElementById('addAccountBtn').addEventListener('click', function () {
                modal.style.display = 'block';
                modalTitle.textContent = 'Add New Account';
                formMethod.value = 'POST';
                form.setAttribute('data-action', 'create');
                form.reset();
            });

            // Open modal for editing
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    modal.style.display = 'block';
                    modalTitle.textContent = 'Edit Account';
                    formMethod.value = 'PUT';
                    form.setAttribute('data-action', 'edit');
                    document.getElementById('accountUserId').value = this.dataset.id;
                    document.getElementById('accountName').value = this.dataset.name;
                    document.getElementById('accountEmail').value = this.dataset.email;
                    document.getElementById('accountRole').value = this.dataset.role;
                    form.querySelector('input[name=password]').value = '';
                });
            });

            // Close modal
            closeModal.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', e => { if (e.target == modal) modal.style.display = 'none'; });

            // AJAX submit (create & edit)
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const userId = document.getElementById('accountUserId').value;
                const action = form.getAttribute('data-action');
                const method = formMethod.value;
                const url = action === 'edit' ? `/user-manager/${userId}` : `/user-manager`;

                const formData = new FormData(form);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-HTTP-Method-Override': method,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();

                if (data.success) {
                    const user = data.user;
                    const rowHTML = `
                                <tr id="user-row-${user.id}">
                                    <td>${user.id}</td>
                                    <td class="user-name">${user.name}</td>
                                    <td class="user-email">${user.email}</td>
                                    <td class="user-role">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</td>
                                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="${user.id}" data-name="${user.name}" data-email="${user.email}" data-role="${user.role}">
                                            Edit
                                        </button>
                                        <form action="/user-manager/${user.id}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>`;

                    // Append new user in correct tab
                    const targetTable = document.querySelector(`#${user.role}s table tbody`);
                    const existingRow = document.getElementById(`user-row-${user.id}`);
                    if (existingRow) existingRow.remove();
                    targetTable.insertAdjacentHTML('beforeend', rowHTML);

                    // Reattach edit listener
                    document.querySelector(`#user-row-${user.id} .edit-btn`).addEventListener('click', function () {
                        modal.style.display = 'block';
                        modalTitle.textContent = 'Edit Account';
                        formMethod.value = 'PUT';
                        form.setAttribute('data-action', 'edit');
                        document.getElementById('accountUserId').value = this.dataset.id;
                        document.getElementById('accountName').value = this.dataset.name;
                        document.getElementById('accountEmail').value = this.dataset.email;
                        document.getElementById('accountRole').value = this.dataset.role;
                        form.querySelector('input[name=password]').value = '';
                    });

                    modal.style.display = 'none';
                    alert('Operation successful!');
                } else {
                    alert('Something went wrong.');
                }
            });
        });
    </script>
    <script>
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function () {
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active', 'show'));
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('data-bs-target'));
                target.classList.add('active', 'show');
            });
        });
    </script>

@endpush