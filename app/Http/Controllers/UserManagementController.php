<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $title = 'Manajemen User';
        $description = 'Kelola user login sistem';
        $users = User::orderBy('name')->get();

        return view('pages.clinic.users.index', compact('title', 'description', 'users'));
    }

    public function create()
    {
        $title = 'Tambah User Baru';
        $description = 'Form tambah user login';
        $roles = $this->getRoles();

        return view('pages.clinic.users.create', compact('title', 'description', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        User::create($validated);

        return redirect()->route('clinic.users.index', app()->getLocale())
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit($language, $id)
    {
        $title = 'Edit User';
        $description = 'Form edit user login';
        $user = User::findOrFail($id);
        $roles = $this->getRoles();

        return view('pages.clinic.users.edit', compact('title', 'description', 'user', 'roles'));
    }

    public function update(Request $request, $language, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->route('clinic.users.index', app()->getLocale())
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroy($language, $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('clinic.users.index', app()->getLocale())
                ->with('error', 'Tidak dapat menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('clinic.users.index', app()->getLocale())
            ->with('success', 'User berhasil dihapus!');
    }

    private function getRoles()
    {
        return [
            'admin' => 'Administrator',
            'doctor' => 'Dokter',
            'cashier' => 'Kasir',
            'staff' => 'Staff',
        ];
    }
}
