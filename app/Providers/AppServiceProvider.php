<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // teknisi, admin, owner : boleh melihat data servis
        Gate::define('lihat-servis', fn (User $u) => in_array($u->role, ['teknisi', 'admin', 'owner'], true));

        // admin, owner : boleh memasukkan data & memperbarui status perbaikan
        Gate::define('kelola-servis', fn (User $u) => in_array($u->role, ['admin', 'owner'], true));

        // admin, owner : boleh membuat/mengunduh cadangan data
        Gate::define('buat-cadangan', fn (User $u) => in_array($u->role, ['admin', 'owner'], true));

        // owner saja
        Gate::define('backup-lengkap', fn (User $u) => $u->role === 'owner');
        Gate::define('pulihkan-data', fn (User $u) => $u->role === 'owner');
        Gate::define('hapus-servis', fn (User $u) => $u->role === 'owner');
        Gate::define('kelola-pengguna', fn (User $u) => $u->role === 'owner');
        Gate::define('lihat-dashboard', fn (User $u) => $u->role === 'owner');
        Gate::define('lihat-audit', fn (User $u) => $u->role === 'owner');
    }
}
