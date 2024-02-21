<?php

namespace AND48\TableFilters\Models;

use AND48\TableFilters\Exceptions\TableFiltersException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'field',
        'type',
        'caption',
        'source_model',
        'scope',
        'options',
    ];

    const TYPE_NUMBER = 'number';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_ENUM = 'enum';
    const TYPE_SOURCE = 'source';

    const TYPES = [self::TYPE_NUMBER, self::TYPE_STRING, self::TYPE_BOOLEAN, self::TYPE_DATE, self::TYPE_ENUM, self::TYPE_SOURCE];

    protected $casts = [
        'options' => 'array',
    ];

    protected static function newFactory()
    {
        return \AND48\TableFilters\Database\Factories\FilterFactory::new();
    }

    public function sourceData(int $page = 1, string $search_query = null){
        if ($this->type !== self::TYPE_SOURCE){
            throw new TableFiltersException('The type must be a "'.self::TYPE_SOURCE.'", "'.$this->type.'" given.', 200);
        }
        if (!$this->source_model || !class_exists($this->source_model)){
            throw new TableFiltersException('Class "'.$this->source_model.'" not exists.', 201);
        }

        $model = app($this->source_model)::getTableFilterModel();
        $source_field = DB::raw($model::getTableFilterSourceField());
        $order_by = DB::raw($model::getTableFilterSourceOrderBy());
        $load = $model::getTableFilterSourceLoad();
        $per_page = config('filters.source_data_per_page');

        $query = $model::select($model::getTableFilterSourceKeyName(), DB::raw($model::getTableFilterSourceField().' AS name'));

        if ($search_query) {
            $query->where($source_field, 'LIKE', "%$search_query%");
        }
        if (!empty($load)) {
            $query->with($load);
        }

        if ($this->scope){
            $query->{$this->scope}();
        }

        $offset = ($page - 1) * $per_page;
        return $query->tableFilterSource()
            ->skip($offset)
            ->take($per_page)
            ->orderBy($order_by)
            ->get();
    }

    private static function numberval($value){
        return floatval($value) + 0;
    }

    private static function dateval($value){
        return Carbon::parse($value);
    }

    public function formatValues($values){
        $formats = [
            self::TYPE_NUMBER => 'self::numberval',
            self::TYPE_STRING => 'strval',
            self::TYPE_BOOLEAN => 'boolval',
            self::TYPE_DATE => 'self::dateval',
//            self::TYPE_ENUM => 'intval',
            self::TYPE_SOURCE => 'intval',
        ];

        if (!isset($formats[$this->type])){
            return $values;
        }
        if (!is_array($values)){
            return call_user_func($formats[$this->type], $values);
        }
        return array_map($formats[$this->type], $values);
    }
}
