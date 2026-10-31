<?php

namespace App\Http\Controllers;

use App\Models\Role;
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
        $users = User::with('roleModel')->orderBy('name')->get();

        return view('pages.clinic.users.index', compact('title', 'description', 'users'));
    }

    public function create()
    {
        $title = 'Tambah User Baru';
        $description = 'Form tambah user login';
        $roles = $this->assignableRoles();

        return view('pages.clinic.users.create', compact('title', 'description', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in($this->assignableRoles()->keys())],
            'is_active' => 'boolean',
        ], [
            'role.in' => 'Role tidak valid atau Anda tidak boleh memberikan role tersebut.',
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->role = $validated['role'];
        $user->is_active = $request->boolean('is_active');
        $user->save();

        return redirect()->route('clinic.users.index', app()->getLocale())
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit($language, $id)
    {
        $title = 'Edit User';
        $description = 'Form edit user login';
        $user = User::findOrFail($id);
        $this->authorizeTarget($user);
        $roles = $this->assignableRoles();

        return view('pages.clinic.users.edit', compact('title', 'description', 'user', 'roles'));
    }

    public function update(Request $request, $language, $id)
    {
        $user = User::findOrFail($id);
        $this->authorizeTarget($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in($this->assignableRoles()->keys())],
            'is_active' => 'boolean',
        ], [
            'role.in' => 'Role tidak valid atau Anda tidak boleh memberikan role tersebut.',
        ]);

        $isActive = $request->boolean('is_active');

        if ($user->id === auth()->id() && ! $isActive) {
            return back()->withInput()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        if ($this->wouldRemoveLastAdmin($user, $validated['role'], $isActive)) {
            return back()->withInput()->with('error', 'Harus ada minimal satu Administrator yang aktif.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->role = $validated['role'];
        $user->is_active = $isActive;
        $user->save();

        return redirect()->route('clinic.users.index', app()->getLocale())
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroy($language, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('clinic.users.index', app()->getLocale())
                ->with('error', 'Tidak dapat menghapus akun sendiri!');
        }

        $this->authorizeTarget($user);

        if ($this->wouldRemoveLastAdmin($user, null, false)) {
            return redirect()->route('clinic.users.index', app()->getLocale())
                ->with('error', 'Harus ada minimal satu Administrator yang aktif.');
        }

        $user->delete();

        return redirect()->route('clinic.users.index', app()->getLocale())
            ->with('success', 'User berhasil dihapus!');
    }

    /**
     * Roles the current user may hand out: an administrator can assign any role,
     * other managers only roles whose permissions they already have themselves.
     *
     * @return \Illuminate\Support\Collection<string, Role> keyed by role name
     */
    private function assignableRoles()
    {
        $actor = auth()->user();
        $roles = Role::orderByDesc('is_system')->orderBy('label')->get();

        if (! $actor->isSuperAdmin()) {
            $own = $actor->permissions();
            $roles = $roles->filter(fn (Role $role) => ! $role->is_system
                && empty(array_diff($role->effectivePermissions(), $own)));
        }

        return $roles->keyBy('name');
    }

    /**
     * Only an administrator may edit or delete an administrator account.
     */
    private function authorizeTarget(User $target): void
    {
        if ($target->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Administrator yang dapat mengubah akun Administrator.');
        }
    }

    /**
     * True when the change would leave no active administrator.
     */
    private function wouldRemoveLastAdmin(User $user, ?string $newRole, bool $newActive): bool
    {
        if (! $user->isSuperAdmin() || ! $user->is_active) {
            return false;
        }

        $stillAdmin = $newRole !== null
            && (bool) Role::where('name', $newRole)->value('is_system')
            && $newActive;

        if ($stillAdmin) {
            return false;
        }

        $systemRoles = Role::where('is_system', true)->pluck('name');

        return User::whereIn('role', $systemRoles)
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->doesntExist();
    }
}
