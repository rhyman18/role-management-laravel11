<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\FlashAlert;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use FlashAlert;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::paginate(10);
        return view('pages.admin.role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('pages.admin.role.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required','string','max:255', 'unique:roles'],
            'display_name' => ['required','string','max:255'],
            'description' => ['required','string','max:255']
        ]);

        $role = Role::create([
            'name' => $request->input('name'),
            'display_name' => $request->input('display_name'),
            'description'=> $request->input('description')
        ]);

        $role->givePermission($request->input('permissions_id'));

        return redirect()->route('admin.role.index')->with($this->alertCreated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $permissions = Permission::all();
            $rolePermissions = $role->permissions()->pluck('id')->toArray();

            return view('pages.admin.role.edit', compact('role','permissions','rolePermissions'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.role.index')->with($this->alertNotFound());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $role = Role::findOrFail($id);

            $this->validate($request, [
                'name' => ['required','string','max:255',"unique:roles,name,$id"],
                'display_name' => ['required','string','max:255'],
                'description'=> ['required','string','max:255']
            ]);

            $role->update([
                'name' => $request->input('name'),
                'display_name' => $request->input('display_name'),
                'description'=> $request->input('description')
            ]);

            $role->syncPermissions($request->input('permissions_id'));

            return redirect()->route('admin.role.index')->with($this->alertUpdated());
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.role.index')->with($this->alertNotFound());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->removePermissions($role->permissions()->pluck('id')->toArray());

            return redirect()->route('admin.role.index')->with($this->alertDeleted());
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.role.index')->with($this->alertNotFound());
        }
    }
}
