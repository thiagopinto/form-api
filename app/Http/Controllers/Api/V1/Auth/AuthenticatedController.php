<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Exception\ClientException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Sanctum\PersonalAccessToken;
use Exception;

class AuthenticatedController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->with(['roles'])->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciais incorretas'],
                ]);
            }

            $user->tokens()->delete();

            $token = $user->createToken(env('APP_NAME', 'CodeBR'));

            return $this->respondWithToken($token, $user);
        } catch (ValidationException $e) {
            $erros = $e->errors();
            return $this->error('Login failed', 501, $erros);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return true;
    }

    /**
     * Display the specified resource by token.
     *
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $user = Auth::user();
        $user = User::with(['roles'])->find($user->id);
        $user->is_superuser = false;

        foreach ($user->roles as $role) {
            if ($role->name == 'admin') {
                $user->is_superuser = true;
            }
        }

        return $user;

        /*if (!Gate::authorize('is-admin', $user)) {
        return response()->json(['error' => 'Not authorized.'], 403);
        } */
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        // $header = $request->header('Authorization');
        //$accessToken = $request->bearerToken();
        $accessToken = $request->has('refresh_token') ? $request->get('refresh_token') : null;

        try {
            if ($accessToken) {
                $token = PersonalAccessToken::findToken($accessToken);
                $user = $token->tokenable_type::find($token->tokenable_id);
                $user->withAccessToken($token);
            }

            $token->delete();

            $token = $user->createToken(env('APP_NAME', 'CodeBR'));

            return $this->respondWithToken($token, $user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }


        /*
        $request->user()->currentAccessToken()->delete();

        $user = $request->user();
        $token = $user->createToken(env('APP_NAME', 'CodeBR'));

        return $this->respondWithToken($token, $user);
        */
    }

    protected function respondWithToken($token, $user)
    {
        return $this->success(
            [
                'access_token' => $token->plainTextToken,
                'token_type' => 'bearer',
                'expires_in' => config('sanctum.expiration') * 60,
                'user' => $user
            ],
            'Login success'
        );
    }

    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        try {
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (ClientException $exception) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        $ifExist = User::where('email', $user->getEmail())->first();

        $userCreated = User::updateOrCreate(
            [
                'email' => $user->getEmail()
            ],
            [
                'email_verified_at' => now(),
                'name' => $user->getName(),
                'status' => true,
                'avatar' => $user->getAvatar()
            ]
        );
        $userCreated->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $user->getId(),
            ],
            [
                'avatar' => $user->getAvatar()
            ]
        );

        if ($ifExist == null) {
            $roleGuest = Role::where('name', 'guest')->first();
            $userCreated->roles()->attach($roleGuest->id);
        }

        $userCreated->tokens()->delete();
        $token = $userCreated->createToken('tabsus');

        $response = $this->respondWithToken($token, $userCreated);
        $urlResponse = env('FRONTEND_URL');
        $urlToken = urlencode($token->plainTextToken);
        return redirect("{$urlResponse}/user/{$urlToken}");
    }

    /**
     * @param $provider
     * @return JsonResponse
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'github', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, github or google'], 422);
        }
    }
}
