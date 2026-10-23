<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\TopbarComposer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const ABILITIES = [
        'enter-expenses' => ['owner', 'manager', 'data_entry'],
        'edit-expenses' => ['owner', 'manager'],
        'manage-seasons' => ['owner', 'manager'],
        'reopen-seasons' => ['owner'],
        'record-payments' => ['owner', 'manager'],
        'enter-production' => ['owner', 'manager', 'data_entry'],
        'manage-production' => ['owner', 'manager'],
        'manage-machinery' => ['owner', 'manager'],
        'manage-settings' => ['owner', 'manager'],
        'manage-users' => ['owner'],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blueprint::macro('auditColumns', function () {
            /** @var Blueprint $this */
            $this->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $this->timestamps();
        });

        foreach (self::ABILITIES as $ability => $roles) {
            Gate::define($ability, fn (User $user) => $user->is_active && $user->hasRole(...$roles));
        }

        View::composer('components.layouts.topbar', TopbarComposer::class);

        Blade::directive('money', fn (string $expression) => "<?php echo e(\App\Support\Money::format($expression)); ?>");
    }
}
