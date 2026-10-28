<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * UC-03: Admin mengelola data pengguna (tambah, ubah, hapus).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $role = $request->query('role');

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, ['admin', 'staff']), fn ($query) => $query->where('role', $role))
            ->withCount('documents')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'totalUsers' => User::count(),
            'totalAdmin' => User::where('role', 'admin')->count(),
            'totalStaff' => User::where('role', 'staff')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => 'staff'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Admin tidak boleh menurunkan role akunnya sendiri (bisa terkunci dari panel admin)
        if ($user->is(auth()->user()) && $data['role'] !== 'admin') {
            return back()->withInput()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        // Password kosong = tidak diubah
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Dokumen ikut terhapus (cascade) jika user dihapus, jadi cegah selama masih punya dokumen
        if (Document::where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Pengguna masih memiliki dokumen, tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
