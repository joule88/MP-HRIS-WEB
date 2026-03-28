<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    const ROLE_KRITIS = ['manajer', 'manager', 'supervisor', 'hrd', 'super_admin', 'staff'];

    public function index(Request $request)
    {
        $query = Role::with(['permissions'])->withCount('users');

        if ($request->filled('search')) {
            $query->where('nama_role', 'like', '%' . $request->search . '%');
        }

        $roles = $query->latest('id_role')->paginate(10)->withQueryString();
        $allPermissions = \App\Models\Permission::orderBy('nama_permission', 'asc')->get();

        return view('role.index', compact('roles', 'allPermissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());

        if ($request->has('id_permissions')) {
            $role->permissions()->sync($request->id_permissions);
        }

        return redirect()->route('role.index')
            ->with('success', 'Role baru berhasil ditambahkan.');
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);

        if (in_array(strtolower($role->nama_role), self::ROLE_KRITIS)) {
            return redirect()->route('role.index')
                ->with('error', 'Role sistem tidak dapat diubah.');
        }

        $role->update($request->validated());

        if ($request->has('id_permissions')) {
            $role->permissions()->sync($request->id_permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('role.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (in_array(strtolower($role->nama_role), self::ROLE_KRITIS)) {
            return redirect()->back()->with('error', 'Role sistem tidak dapat dihapus.');
        }

        if ($role->users()->exists()) {
            return redirect()->back()->with('error', 'Role tidak bisa dihapus karena masih digunakan user.');
        }

        $role->delete();

        return redirect()->route('role.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}