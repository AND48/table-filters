<?php

namespace AND48\TableFilters;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class TableFiltersServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'filters');
    }

    public function boot()
    {
        //
        if ($this->app->runningInConsole()) {
            // Export the migration
            if (! class_exists('CreateFiltersTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_filters_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_filters_table.php'),
                    __DIR__ . '/../database/migrations/create_filter_storages_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_filter_storages_table.php'),
                ], 'migrations');
            }

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('filters.php'),
            ], 'config');

            $this->registerRoutes();
        }
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('filters.prefix'),
            'middleware' => config('filters.middleware'),
        ];
    }
}
