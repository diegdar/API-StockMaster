<?php

namespace App\Providers;

use App\Models\{
    Category,
    StockMovement,
    Warehouse
};
use App\Observers\{
    CategoryObserver,
    StockMovementObserver,
    WarehouseObserver
};
use Dedoc\Scramble\{
    Scramble,
    Support\Generator\OpenApi,
    Support\Generator\SecurityScheme,

};
use Illuminate\{
    Cache\RateLimiting\Limit,
    Http\Request,
    Support\Facades\RateLimiter,
    Support\Facades\Schema,
    Support\ServiceProvider,
    Support\Facades\Gate,
};
use App\Repositories\{
    CategoryRepository,
    ProductRepository,
    StockMovementRepository,
    WarehouseRepository,
    Contracts\StockMovementRepositoryInterface,
    Contracts\CategoryRepositoryInterface,
    Contracts\ProductRepositoryInterface,
    Contracts\WarehouseRepositoryInterface
};

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
        Category::observe(CategoryObserver::class);
        Warehouse::observe(WarehouseObserver::class);

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
