<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Exception;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed'],
            ]);

            // , Rules\Password::defaults()

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->has('phone') ? $request->phone : null,
                'occupation' => $request->has('occupation') ? $request->occupation : null,
                'password' => Hash::make($request->password),
            ]);

            if ($request->has('avatar')) {
                $user->avatar = $request->file('avatar')->store('public/avatar');
            }
            /*
            foreach ($request->roles as $role) {
                $user->roles()->attach($role);
            }
            */
            $roleGuest = Role::where('name', 'guest')->first();
            $user->roles()->attach($roleGuest->id);


            event(new Registered($user));

            Auth::login($user);

            $user->tokens()->delete();
            $token = $user->createToken("login:user{$user->id}")->plainTextToken;

            return $this->success(['access_token' => $token, 'user' => $user], 'User creanted');
        } catch (ValidationException $e) {
            $erros = $e->errors();
            return $this->error('User not creanted', 501, $erros);
        }
    }
}
