<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Login activity in the audit log (passwords are never stored)
        Event::listen(Login::class, function (Login $event) {
            $this->audit('login', $event->user, 'Login', [], $event->user);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                $this->audit('logout', $event->user, 'Logout', [], $event->user);
            }
        });

        // Failed attempt: nobody is logged in; the account (if it exists) is the subject
        Event::listen(Failed::class, function (Failed $event) {
            $email = $event->credentials['email'] ?? null;
            $this->audit('login_failed', $event->user, 'Login gagal: ' . ($email ?? '-'), ['email' => $email]);
        });
    }

    private function audit(string $event, $user, string $description, array $new = [], $actor = null): void
    {
        try {
            AuditLog::record($event, $user, [], $new, $description, $actor instanceof \App\Models\User ? $actor : null);
        } catch (\Throwable $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }
}
