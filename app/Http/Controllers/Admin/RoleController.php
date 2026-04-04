<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('backend.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('backend.roles.create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('backend.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        if ($role->name === 'Super Admin' && $validated['name'] !== 'Super Admin') {
            return back()->with('error', 'Role Super Admin tidak boleh diubah namanya.');
        }

        $role->update([
            'name' => $validated['name'],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Role Super Admin tidak boleh dihapus.');
        }

        $hasUsers = DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('role_id', $role->id)
            ->exists();

        if ($hasUsers) {
            return back()->with('error', 'Role ini masih dipakai oleh user.');
        }

        $role->delete();

        return back()->with('success', 'Role berhasil dihapus.');
    }
}
