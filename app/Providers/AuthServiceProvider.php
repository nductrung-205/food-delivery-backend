<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('isAdmin', fn(User $user) => $user->role === User::ROLE_ADMIN);
        Gate::define('isUser', fn(User $user) => $user->role === User::ROLE_USER);
    }
}
