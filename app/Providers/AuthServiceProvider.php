<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Book::class => \App\Policies\BookPolicy::class,
        \App\Models\Review::class => \App\Policies\ReviewPolicy::class,
        \App\Models\ReadingPlan::class => \App\Policies\ReadingPlanPolicy::class,
    ];

    /**
     * 認可の設定を登録し、personal_access_tokens をアプリのマイグレーションで作るため Sanctum 同梱のマイグレーションを読み込まないようにする。
     */
    public function register(): void
    {
        parent::register();

        Sanctum::ignoreMigrations();
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void {}
}
