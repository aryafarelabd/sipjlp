<?php

namespace App\Providers;

use App\Models\Absensi;
use App\Models\BuktiPekerjaanCs;
use App\Models\Cuti;
use App\Models\JadwalKerjaCsBulanan;
use App\Models\LembarKerja;
use App\Models\LembarKerjaCs;
use App\Models\Pjlp;
use App\Models\User;
use App\Policies\BuktiPekerjaanCsPolicy;
use App\Policies\CutiPolicy;
use App\Policies\LembarKerjaCsPolicy;
use App\Policies\LembarKerjaPolicy;
use App\Policies\PjlpPolicy;
use App\Policies\UserPolicy;
use App\Channels\TelegramChannel;
use App\Services\TelegramService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->make(ChannelManager::class)->extend('telegram', function () {
            return new TelegramChannel(new TelegramService());
        });
    }

    public function boot(): void
    {
        // Use Bootstrap pagination
        Paginator::useBootstrapFive();

        // Register Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Pjlp::class, PjlpPolicy::class);
        Gate::policy(Cuti::class, CutiPolicy::class);
        Gate::policy(LembarKerja::class, LembarKerjaPolicy::class);
        Gate::policy(LembarKerjaCs::class, LembarKerjaCsPolicy::class);
        Gate::policy(BuktiPekerjaanCs::class, BuktiPekerjaanCsPolicy::class);
        Gate::policy(JadwalKerjaCsBulanan::class, BuktiPekerjaanCsPolicy::class);

    }
}
