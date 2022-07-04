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
        'source_model'
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

    public function sourceData(int $page = 1, string $search_query = null){
        if ($this->type !== self::TYPE_SOURCE || !$this->source_model || !class_exists($this->source_model)){
            return collect();
        }

        $model = app($this->source_model);
        $source_field = $model::getFilterSourceField();
        $per_page = $model::getFilterSourcePerPage();
        $order_by = $model::getFilterSourceOrderBy();
        $load = $model::getFilterSourceLoad();

        $query = $model->query();

        if ($search_query) {
            $query = $query->where($source_field, 'LIKE', "%$search_query%");
        }
        if (!empty($load)) {
            $query = $query->with($load);
        }

        $offset = ($page - 1) * $per_page;
        $query = $query->skip($offset)
            ->take($per_page)
            ->orderBy($order_by);

        return $query->get()->transform(function($item) use ($model){
            return $model::getFilterSourceTransform($item);
        });
    }
}
