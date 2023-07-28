<?php

namespace AND48\TableFilters\Models;

use AND48\TableFilters\Exceptions\TableFiltersException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'causer_type',
        'causer_id',
        'name',
        'filters'
    ];

    protected $casts = [
        'filters' => 'json'
    ];

    protected static function newFactory()
    {
        return \AND48\TableFilters\Database\Factories\FilterStorageFactory::new();
    }

    public function causer()
    {
        return $this->morphTo();
    }
}
