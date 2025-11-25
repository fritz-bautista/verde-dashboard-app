<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Admin: Account Manager index.
     * Returns separate collections for each role to populate tabbed tables.
     */
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('created_at', 'desc')->get();
        $colleges = User::where('role', 'college')->orderBy('created_at', 'desc')->get();
        $students = User::where('role', 'student')->orderBy('created_at', 'desc')->get();

        return view('admin.account-manager', compact('admins', 'colleges', 'students'));
    }

    /**
     * Show account settings for the currently authenticated user.
     * Blade: resources/views/user/account-settings.blade.php (or adapt to your path)
     */
    public function accountSettings()
    {
        $user = auth()->user();
        return view('user.account-settings', compact('user'));
    }

    /**
     * Admin: show create form (optional if you use modal)
     */
    public function create()
    {
        return view('admin.create');
    }

    /**
     * Admin: store new account (AJAX-friendly JSON response).
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'college', 'student'])],
            'password' => 'nullable|string|min:6',
            'college_id' => 'nullable|exists:colleges,id' // allow null but must exist if provided
        ]);

        // require college_id if role is student
        if ($request->role === 'student' && !$request->college_id) {
            return response()->json(['success' => false, 'message' => 'College is required for students.'], 422);
        }

        $password = $request->input('password') ?: 'password123';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'college_id' => $request->college_id ?? null,
            'password' => Hash::make($password),
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }


    /**
     * Admin: return edit form (if needed) - else editing is done by modal + update
     */
    public function edit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    /**
     * Admin: update an existing account (AJAX-friendly JSON)
     */

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'college', 'student'])],
            'password' => 'nullable|string|min:6',
            'college_id' => 'nullable|exists:colleges,id'
        ]);

        if ($request->role === 'student' && !$request->college_id) {
            return response()->json(['success' => false, 'message' => 'College is required for students.'], 422);
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'college_id' => $request->college_id ?? null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Admin: delete account.
     * If AJAX request, return JSON; otherwise redirect back with flash.
     */
    public function destroy(Request $request, User $user)
    {
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('account.manager')->with('success', 'Account deleted successfully.');
    }

    /**
     * Logged-in user: update their own account (account settings page).
     * Route name used elsewhere in your app: account.update.self (suggested).
     */
    public function updateOwnAccount(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Account updated successfully!');
    }
}
