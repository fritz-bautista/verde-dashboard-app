<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            @if ($role === 'student')
                <th>College</th>
            @endif
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        <tr id="user-row-{{ $user->id }}">
            <td>{{ $user->id }}</td>
            <td class="user-name">{{ $user->name }}</td>
            <td class="user-email">{{ $user->email }}</td>
            <td class="user-role">{{ ucfirst($user->role) }}</td>
            @if ($role === 'student')
                <td class="user-college">{{ $user->college?->name ?? '-' }}</td>
            @endif
            <td>{{ $user->created_at->format('M d, Y') }}</td>
            <td>
                <button class="btn btn-sm btn-warning edit-btn"
                    data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}"
                    data-email="{{ $user->email }}"
                    data-role="{{ $user->role }}"
                    data-college="{{ $user->college_id ?? '' }}">
                    Edit
                </button>
                <form action="/user-manager/{{ $user->id }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
