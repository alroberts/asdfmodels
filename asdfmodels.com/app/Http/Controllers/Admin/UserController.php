<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::where('is_admin', false);

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'model') {
                $query->where('is_photographer', false);
            } elseif ($request->type === 'photographer') {
                $query->where('is_photographer', true);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only(['type', 'search']),
        ]);
    }

    /**
     * Show a specific user.
     */
    public function show(string $id): View
    {
        $user = User::with('modelProfile')->findOrFail($id);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(string $id): View
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_photographer' => ['boolean'],
            'is_admin' => ['boolean'],
        ]);

        // Only allow setting admin if current user is admin
        if ($request->has('is_admin') && !Auth::user()->is_admin) {
            unset($validated['is_admin']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.show', $user->id)
            ->with('status', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->is_admin) {
            return back()->withErrors(['user' => 'Cannot delete admin users.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }
}

