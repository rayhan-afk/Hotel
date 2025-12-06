<?php

namespace App\Providers;

use App\Models\Transaction; // 💡 TAMBAHKAN INI
use App\Policies\TransactionPolicy; // 💡 TAMBAHKAN INI
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',

        // 💡 DAFTARKAN POLICY TRANSAKSI DI SINI
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
