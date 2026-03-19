<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        $roles = Role::with('permissions')->get();
        return view('permission.index', compact('permissions', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_permission' => 'required|unique:permissions,nama_permission',
        ]);

        Permission::create([
            'nama_permission' => $request->nama_permission,
            'slug' => \Illuminate\Support\Str::slug($request->nama_permission)
        ]);

        return back()->with('success', 'Permission berhasil dibuat.');
    }

    public function sync(Request $request, $id_role)
    {
        $role = Role::findOrFail($id_role);
        $role->permissions()->sync($request->permissions);

        return back()->with('success', 'Hak akses role berhasil diperbarui.');
    }
}