<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $user)
    {
        if (!Gate::authorize('is-admin', $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('per_page')) {
            $perPage = $request->input('per_page');
        } else {
            $perPage = 5;
        }

        if ($request->query('search')) {
            $search = $request->query('search');
            $users = User::with(['roles'])->where('name', 'like', '%' . $search . '%')->paginate($perPage);
        } else {
            $users = User::with(['roles'])->paginate($perPage);
        }

        return $users;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        foreach ($request->roles as $role) {
            $user->roles()->attach($role);
        }
        $user->save();

        event(new Registered($user));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $requestUser = $request->user();

        $user = User::with(['roles'])->find($id);

        if (!Gate::authorize('is-admin', $requestUser, $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->occupation = $request->occupation;

        foreach ($request->roles as $role) {
            $user->roles()->detach($role);
            $user->roles()->attach($role);
        }
        $user->save();
        $user = User::with(['roles'])->find($id);

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $requestUser = $request->user();

        $user = User::with(['roles'])->find($id);

        if (!Gate::authorize('is-admin', $requestUser, $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        foreach ($user->roles as $role) {
            $user->roles()->detach($role);
        }

        $user->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function checkEmail($email)
    {
        $user = User::where('email', $email)->first();
        if ($user == null) {
            return false;
        }
        return true;
    }


}
