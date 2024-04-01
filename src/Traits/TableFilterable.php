<?php

namespace AND48\TableFilters\Traits;

use AND48\TableFilters\Exceptions\TableFiltersException;
use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Models\FilterStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trait TableFilterable
 * @package AND48\TableFilters\Traits
 */
trait TableFilterable
{
    protected static function getTableFilterModel(){
        return self::class;
    }

    protected static function getTableFilterResponseFields(){
        return config('filters.response_fields');
    }

    public static function tableFilterList($load_operators = true, $enum_values = []){
        $filters = Filter::where('model', static::getTableFilterModel())->get();
        if ($load_operators){
            $filters->transform(function ($filter) use ($load_operators, $enum_values){
                $filter->operators = config('filters.operators.'.$filter['type']);
                $filter->values = null;
                if ($filter['type'] === Filter::TYPE_ENUM){
                    $filter->values = $enum_values[$filter['field']] ?? [];
                }
                return $filter->only(static::getTableFilterResponseFields());
            });
        }
        return $filters;
    }

    public static function addTableFilter($filter){
        $filter['model'] = static::getTableFilterModel();
        if (!isset($filter['field'])){
            $filter['field'] = 'id';
        }
        if (!isset($filter['type'])){
            $filter['type'] = Filter::TYPE_STRING;
        }
        if (!isset($filter['caption'])){
            $filter['caption'] = '';
        }

        if ($filter['type'] === Filter::TYPE_SOURCE && (!isset($filter['source_model']) || !class_exists($filter['source_model']))){
            throw new TableFiltersException('Class "'.($filter['source_model'] ?? '').'" not exists.', 100);
        }

        return Filter::firstOrCreate($filter);
    }

    public static function addTableFilters($filters){
        foreach ($filters as $filter){
            self::addTableFilter($filter);
        }
    }

    /**
     *
     * get field name for source data
     *
     * @return string
     */
    public static function getTableFilterSourceField() :string{
        $class = static::getTableFilterModel();
        $model =  new $class;
        return $model->getTable().'.name';
    }

    /**
     *
     * get static keyName for source data
     *
     * @return string
     */
    public static function getTableFilterSourceKeyName() :string{
        return (new static())->getKeyName();
    }

    /**
     *
     * get sorting field for source data
     *
     * @return string
     */
    public static function getTableFilterSourceOrderBy():string{
        $class = static::getTableFilterModel();
        $model =  new $class;
        return $model->getTable().'.'.static::getTableFilterSourceKeyName();
    }

    /**
     *
     * get lazy load array for source data
     *
     * @return array
     */
    public static function getTableFilterSourceLoad() :array{
        return [];
    }

    /**
     *
     * get transform method for source data
     *
     * @return array
     */
//    public static function getTableFilterSourceTransform($item) :array{
//        return $item->only([static::getTableFilterSourceKeyName(), static::getTableFilterSourceField()]);
//    }

    /**
     *
     * scope for source data
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTableFilterSource($query) :Builder{
        return $query;
    }

    /**
     *
     * filter scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTableFilter($query, array $request = []) :Builder{
        $request = array_map(function($params){
            if (is_object($params)) {
                return (array)$params;
            } elseif (is_string($params)){
                $original_params = $params;
                $params = json_decode($params);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new TableFiltersException('Invalid filter "'.$original_params.'"', 302);
                }
                return (array)$params;
            } elseif(is_array($params)) {
                return $params;
            } else {
                throw new TableFiltersException('Invalid filter format', 303);
            }
        }, $request);

        $filters = Filter::where('model', static::getTableFilterModel())->find(Arr::pluck($request, 'id'));
        if ($filters->isEmpty()) {
            return $query;
        }

        foreach ($request as $params){
            $filter = $filters->find($params['id']);
            if (method_exists($this, Str::camel($filter->field).'TableFilterable')) {
                $filter->field = DB::raw(call_user_func([$this, Str::camel($filter->field).'TableFilterable']));
            } elseif(!Str::contains($filter->field, '.')){
                $filter->field = $this->getTable().'.'.$filter->field;
            }

            if (array_search($params['operator'], config('filters')['operators'][$filter->type]) === false){
                throw new TableFiltersException('Operator "'.$params['operator'].'" not configured for type "'.$filter->type.'"', 300);
            }
            if ($filter->type === Filter::TYPE_SOURCE && (!$filter->source_model || !class_exists($filter->source_model))){
                throw new TableFiltersException('Class "'.$filter->source_model.'" not exists.', 301);
            }

//            if (!is_array($params['values'])){
//                continue;
//            }

            if (empty($params['values'])){
                if ($params['operator'] === '!='){
                    $query->whereNotNull($filter->field);
                } else {
                    $query->whereNull($filter->field);
                }
                continue;
            }

            if (is_array($params['values']) && (
                    $filter->type === Filter::TYPE_BOOLEAN
                    || count($params['values']) == 1
                    || array_search($params['operator'], ['<','<=','>','>=']) !== false
                )){
                $params['values'] = Arr::first($params['values']);
            }

            $params['values'] = $filter->formatValues($params['values']);

            switch ($params['operator'] ?? '=') {
                case '=':
                    if (!is_array($params['values'])){
                        $query->where($filter->field, $params['values']);
                    } else {
                        $query->whereIn($filter->field, $params['values']);
                    }
                    break;
                case '!=':
                    if (!is_array($params['values'])){
                        $query->where($filter->field, '!=', $params['values']);
                    } else {
                        $query->whereNotIn($filter->field, $params['values']);
                    }
                    break;
                case '<':
                case '<=':
                case '>':
                case '>=':
                    $query->where($filter->field, $params['operator'], $params['values']);
                    break;
                case '~':
                    if (!is_array($params['values'])){
                        $query->where($filter->field, 'LIKE', "%".$params['values']."%");
                    } else {
                        $query->where(function ($query) use ($filter, $params) {
                            foreach ($params['values'] as $value) {
                                $query->orWhere($filter->field, 'LIKE', "%$value%");
                            }
                        });
                    }
                    break;
            }

        }

        return $query;
    }

    protected static function getTableFilterStorageResponseFields(){
        return ['id','name','rules'];
    }

    public static function tableFilterStorageList($user){
        $storages = FilterStorage::
            select(static::getTableFilterStorageResponseFields())
            ->where('causer_type', $user->getMorphClass())
            ->where('model', static::getTableFilterModel())
            ->where(function($query) use ($user){
                $query->whereNull('causer_id')->orWhere('causer_id', $user->id);
            })->get();
        return $storages;
    }
}
