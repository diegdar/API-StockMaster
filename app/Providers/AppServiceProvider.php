<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Observers\SlugObserver;
use App\Observers\StockMovementObserver;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\WarehouseRepository;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Observers
        StockMovement::observe(StockMovementObserver::class);
        Category::observe(SlugObserver::class);
        Supplier::observe(SlugObserver::class);
        Warehouse::observe(SlugObserver::class);

        // Rate Limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // enable Scramble authentication for production (public)
        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });

        // Add Bearer token security scheme to OpenAPI documentation
        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
                    ->as('bearerAuth')
                    ->setDescription('Enter your Bearer token to access protected endpoints')
            );
        });
    }
}
