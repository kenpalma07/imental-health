<?php

namespace Modules\IAM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Modules\Core\Http\Controllers\CoreController as Controller;
use Modules\IAM\Models\Role;
use Modules\IAM\Models\User;
use Modules\IAM\Http\Requests\UserFormRequest;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return Inertia
     */
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? self::ITEMS_PER_PAGE;

        $roles = Role::all('id', 'name');

        return Inertia::render('IAM::User/index', [
            'perPage' => (int) $perPage,
            'filters' => $request->all('search'),
            'users' => User::with('roles')->select('id', 'name_suffix', 'first_name', 'middle_name', 'last_name', 'username', 'email', 'is_approve', 'is_lock', 'is_active')->get(),
            'columns' => ['full_name', 'username', 'email', 'is_approve', 'is_lock', 'is_active'],
            'can' => [
                'create' => Auth::user()->can('create', User::class),
                'assignRole' => Auth::user()->can('assignRole', User::class),
            ],
            'check' => Auth::user()->hasRole('Administrator') ?: false,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Inertia
     */
    public function create()
    {
        return Inertia::render('User::User/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  UserFormRequest  $request
     * @return Redirect
     */
    public function store(UserFormRequest $request): RedirectResponse
    {
        $attributes = $request->safe()->except('password');

        $user = User::create(array_merge($attributes, [
            'password' => Hash::make($request->password),
        ]));

        if (isset($request->photo)) {
            $user->updateProfilePhoto($request->photo);
        }

        /* if(isset($request->roles)) {
            // $roles = Role::findMany($request->roles);
            $roleIds = Arr::pluck($request->roles, 'id');
            $user->assignRole($roleIds);
        } */

        return redirect(route('admin.users.index'))->with('success', 'User created.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User  $user
     * @return Inertia
     */
    public function edit(User $user)
    {
        return Inertia::render('User::User/Edit', [
            'user' => $user->load('roles:id,name'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UserFormRequest  $request
     * @param  User  $user
     * @return Redirect
     */
    public function update(UserFormRequest $request, User $user): RedirectResponse
    {
        if (isset($request->photo)) {
            $user->updateProfilePhoto($request->photo);
        }

        $user->update($request->validated());

        /* if(isset($request->roles)) {
            // $roles = Role::findMany($request->roles);
            $roleIds = Arr::pluck($request->roles, 'id');
            $user->syncRoles($roleIds);
        } */

        // return redirect()->back();
        return redirect(route('admin.users.index'))->with('success', 'User updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     * @return Redirect
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect(route('admin.users.index'))->with('success', 'User deleted.');
    }

    /**
     * Restore specified resource in storage.
     *
     * @param  User  $user
     * @return Redirect
     */
    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        return redirect(route('admin.users.index'))->with('success', 'User restored.');
    }

    /**
     * Delete user profile photo
     *
     * @param  User  $user
     * @return Redirect
     */
    public function deletePhoto(User $user): RedirectResponse
    {
        $user->deleteProfilePhoto();

        return back(303)->with('status', 'profile-photo-deleted');
    }
}
