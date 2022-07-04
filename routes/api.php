<?php

use Illuminate\Support\Facades\Route;
use AND48\TableFilters\Http\Controllers\FilterController;

Route::get('/{filter_id}/source-data', [FilterController::class, 'sourceData'])
    ->name('filters.source_data')
    ->where('filter_id', '[0-9]+');
