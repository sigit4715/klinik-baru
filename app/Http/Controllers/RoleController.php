<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Manajemen Role: create roles and tick which menus/actions each role may use.
 * Combining jobs (e.g. "Kasir + Apoteker") is simply a role with both sets ticked.
 */
class RoleController extends Controller
{
    public function index()
    {
        $title = 'Manajemen Role';
        $description = 'Atur hak akses menu untuk setiap role';
        $roles = Role::withCount('users')->orderByDesc('is_system')->orderBy('label')->get();
        $totalPermissions = count(Role::allPermissionKeys());

        return view('pages.clinic.roles.index', compact('title', 'description', 'roles', 'totalPermissions'));
    }

    public function create(Request $request)
    {
        $title = 'Tambah Role';
        $description = 'Buat role baru dan pilih hak aksesnya';
        $role = new Role(['permissions' => []]);

        // "Salin dari role": start from the permissions of one or more existing roles
        $copyFrom = Role::whereIn('id', (array) $request->query('copy', []))->get();
        if ($copyFrom->isNotEmpty()) {
            $role->permissions = $copyFrom->flatMap->effectivePermissions()->unique()->values()->all();
        }

        return view('pages.clinic.roles.form', $this->formData($title, $description, $role));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:roles,name'],
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(Role::allPermissionKeys())],
        ], [
            'name.regex' => 'Kode role hanya boleh huruf kecil, angka, dan garis bawah (contoh: kasir_apoteker).',
        ]);

        $permissions = $this->guardPermissions($validated['permissions'] ?? []);

        $role = new Role();
        $role->name = $validated['name'];
        $role->label = $validated['label'];
        $role->description = $validated['description'] ?? null;
        $role->permissions = $permissions;
        $role->save();

        return redirect()->route('clinic.roles.index', app()->getLocale())
            ->with('success', "Role {$role->label} berhasil dibuat.");
    }

    public function edit($language, $id)
    {
        $role = Role::findOrFail($id);
        $this->authorizeRole($role);
        $title = 'Edit Role';
        $description = 'Ubah hak akses role ' . $role->label;

        return view('pages.clinic.roles.form', $this->formData($title, $description, $role));
    }

    public function update(Request $request, $language, $id)
    {
        $role = Role::findOrFail($id);
        $this->authorizeRole($role);

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(Role::allPermissionKeys())],
        ]);

        $role->label = $validated['label'];
        $role->description = $validated['description'] ?? null;

        // The system role always has full access; its permission list is not editable
        if (! $role->is_system) {
            $role->permissions = $this->guardPermissions($validated['permissions'] ?? []);
        }

        $role->save();

        return redirect()->route('clinic.roles.index', app()->getLocale())
            ->with('success', "Role {$role->label} berhasil diperbarui.");
    }

    public function destroy($language, $id)
    {
        $role = Role::withCount('users')->findOrFail($id);
        $this->authorizeRole($role);

        if ($role->is_system) {
            return back()->with('error', 'Role sistem tidak bisa dihapus.');
        }

        if ($role->users_count > 0) {
            return back()->with('error', "Role {$role->label} masih dipakai {$role->users_count} user. Pindahkan user ke role lain terlebih dahulu.");
        }

        $role->delete();

        return redirect()->route('clinic.roles.index', app()->getLocale())
            ->with('success', "Role {$role->label} berhasil dihapus.");
    }

    private function formData(string $title, string $description, Role $role): array
    {
        $actor = auth()->user();

        return [
            'title' => $title,
            'description' => $description,
            'role' => $role,
            'groups' => config('permissions'),
            'selected' => $role->exists ? $role->effectivePermissions() : ($role->permissions ?? []),
            // Non-admin managers can only grant what they have themselves
            'grantable' => $actor->isSuperAdmin() ? Role::allPermissionKeys() : $actor->permissions(),
            'otherRoles' => Role::where('is_system', false)->when($role->exists, fn ($q) => $q->where('id', '!=', $role->id))->orderBy('label')->get(),
        ];
    }

    /**
     * Prevent privilege escalation: a non-admin may only grant permissions they hold.
     */
    private function guardPermissions(array $permissions): array
    {
        $permissions = array_values(array_unique($permissions));
        $actor = auth()->user();

        if (! $actor->isSuperAdmin()) {
            $notAllowed = array_diff($permissions, $actor->permissions());
            if ($notAllowed) {
                abort(403, 'Anda tidak bisa memberikan hak akses yang tidak Anda miliki.');
            }
        }

        return $permissions;
    }

    /**
     * Only an administrator may edit the system role or roles with more access than themselves.
     */
    private function authorizeRole(Role $role): void
    {
        $actor = auth()->user();
        if ($actor->isSuperAdmin()) {
            return;
        }

        if ($role->is_system || array_diff($role->effectivePermissions(), $actor->permissions())) {
            abort(403, 'Anda tidak bisa mengubah role yang memiliki akses lebih tinggi dari Anda.');
        }
    }
}
