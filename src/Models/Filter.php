<?php

namespace AND48\TableFilters\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'field',
        'type',
        'caption',
//        'related_model'
    ];

    const TYPE_NUMBER = 'number';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_ENUM = 'enum';
    const TYPE_SOURCE = 'source';

    const TYPES = [self::TYPE_NUMBER, self::TYPE_STRING, self::TYPE_BOOLEAN, self::TYPE_DATE, self::TYPE_ENUM, self::TYPE_SOURCE];

    protected static function newFactory()
    {
        return \AND48\TableFilters\Database\Factories\FilterFactory::new();
    }
}
