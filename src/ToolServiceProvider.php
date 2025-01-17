<?php

namespace KABBOUCHI\NovaImpersonate;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-impersonate', __DIR__.'/../dist/js/field.js');
        Nova::style('nova-impersonate', __DIR__.'/../dist/css/field.css');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-impersonate');

        $this->publishes([__DIR__.'/../resources/views' => base_path('resources/views/vendor/nova-impersonate'),
        ], 'nova-impersonate-views');

        $this->publishes([__DIR__.'/../config/nova-impersonate.php' => config_path('nova-impersonate.php'),
        ], 'nova-impersonate-config');

        $this->app->booted(function () {
            if (config('nova-impersonate.enable_middleware')) {
                $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(\KABBOUCHI\NovaImpersonate\Http\Middleware\Impersonate::class);
            }
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(array_wrap(config('nova-impersonate.middleware.base')))
            ->prefix('nova-impersonate')
            ->name('nova.impersonate.')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nova-impersonate.php', 'nova-impersonate');
    }
}
