<?php

namespace AND48\TableFilters;

use Illuminate\Support\ServiceProvider;

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
                ], 'migrations');
            }

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('filters.php'),
            ], 'config');
        }
    }
}
