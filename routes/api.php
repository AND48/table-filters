<?php

use Illuminate\Support\Facades\Route;
use AND48\TableFilters\Http\Controllers\FilterController;

Route::get('/{filter}/source-data', [FilterController::class, 'sourceData'])->name('filters.source_data');
