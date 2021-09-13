<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = env('APP_SPA');

            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $frontendUrl . '?verify_url=' . $verifyUrl;
        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifique o endereço de e-mail')
                ->line('Clique no botão abaixo para verificar o seu endereço de e-mail.')
                ->action('Verifique o endereço de e-mail', $url)
                ->line('Se você não criou uma conta, nenhuma ação adicional é necessária.');
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return env('APP_SPA') . "/auth/reset/confirm/{$token}/{$user->email}";
        });

        /*
        ResetPassword::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Reset Senha')
                ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
                ->action('Reset Senha', $url)
                ->line('Este link de redefinição de senha irá expirar em: minutos de contagem.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')])
                ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional será necessária.');
        }); */

        Gate::define('is-admin', function (User $user, User $currentUser) {
            if ($user->isAdmin() || $user->id === $currentUser->id) {
                return true;
            }
            return false;
        });

        Gate::define('is-staff', function (User $user) {
            if ($user->isStaff()) {
                return true;
            }
            return false;
        });

    }
}
