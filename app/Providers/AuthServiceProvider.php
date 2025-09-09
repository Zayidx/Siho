<?php

namespace App\Providers;

use App\Models\FnbOrder;
use App\Policies\FnbOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FnbOrder::class => FnbOrderPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
