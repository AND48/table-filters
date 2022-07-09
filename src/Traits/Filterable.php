<?php

namespace AND48\TableFilters\Traits;

use AND48\TableFilters\Exceptions\TableFiltersException;
use AND48\TableFilters\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Trait Filterable
 * @package AND48\TableFilters\Traits
 */
trait Filterable
{
    protected static function getFilterModel(){
        return self::class;
    }

    protected static function getFilterResponseFields(){
        return ['id','type','caption','operators', 'values'];
    }

    public static function filterList($load_operators = true, $enum_values = []){
        $filters = Filter::where('model', self::getFilterModel())->get();
        if ($load_operators){
            $filters->transform(function ($filter) use ($load_operators, $enum_values){
                $filter->operators = config('filters.operators.'.$filter['type']);
                $filter->values = null;
                if ($filter['type'] === Filter::TYPE_ENUM){
                    $filter->values = $enum_values[$filter['field']] ?? [];
                }
                return $filter->only(self::getFilterResponseFields());
            });
        }
        return $filters;
    }

    public static function addFilter($filter){
        $filter['model'] = self::getFilterModel();
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

    public static function addFilters($filters){
        foreach ($filters as $filter){
            self::addFilter($filter);
        }
    }

    /**
     *
     * get field name for source data
     *
     * @return string
     */
    public static function getFilterSourceField() :string{
        return 'name';
    }

    /**
     *
     * get static keyName for source data
     *
     * @return string
     */
    public static function getFilterSourceKeyName() :string{
        return (new static())->getKeyName();
    }

    /**
     *
     * get sorting field for source data
     *
     * @return string
     */
    public static function getFilterSourceOrderBy():string{
        return self::getFilterSourceKeyName();
    }

    /**
     *
     * get field count per page for source data
     *
     * @return int
     */
    public static function getFilterSourcePerPage() :int{
        return 10;
    }

    /**
     *
     * get lazy load array for source data
     *
     * @return array
     */
    public static function getFilterSourceLoad() :array{
        return [];
    }

    /**
     *
     * get transform method for source data
     *
     * @return array
     */
    public static function getFilterSourceTransform($item) :array{
        return $item->only([self::getFilterSourceKeyName(), self::getFilterSourceField()]);
    }

    /**
     *
     * scope for source data
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterSource($query) :Builder{
        return $query;
    }

    /**
     *
     * filter scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $request = []) :Builder{
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

        $filters = Filter::where('model', self::class)->find(Arr::pluck($request, 'id'));
        if ($filters->isEmpty()) {
            return $query;
        }

        foreach ($request as $params){
            $filter = $filters->find($params['id']);
            if (array_search($params['operator'], config('filters')['operators'][$filter->type]) === false){
                throw new TableFiltersException('Operator "'.$params['operator'].'" not configured for type "'.$filter->type.'"', 300);
            }
            if ($filter->type === Filter::TYPE_SOURCE && (!$filter->source_model || !class_exists($filter->source_model))){
                throw new TableFiltersException('Class "'.$filter->source_model.'" not exists.', 301);
            }

            if (!is_array($params['values'])){
                continue;
            }

            if (empty($params['values'])){
                if ($params['operator'] === '!='){
                    $query->whereNotNull($filter->field);
                } else {
                    $query->whereNull($filter->field);
                }
                continue;
            }

            if ($this->type === Filter::TYPE_BOOLEAN){
                $params['values'] = Arr::first($params['values']);
            }

            $params['values'] = $filter->formatValues($params['values']);
            switch ($params['operator'] ?? '=') {
                case '=':
                    if (count($params['values']) == 1){
                        $query->where($filter->field, Arr::first($params['values']));
                    } else {
                        $query->whereIn($filter->field, $params['values']);
                    }
                    break;
                case '!=':
                    if (count($params['values']) == 1){
                        $query->where($filter->field, '!=', Arr::first($params['values']));
                    } else {
                        $query->whereNotIn($filter->field, $params['values']);
                    }
                    break;
                case '<':
                case '<=':
                case '>':
                case '>=':
                    $query->where($filter->field, $params['operator'], Arr::first($params['values']));
                    break;
                case '~':
                    $query->where(function ($query) use ($filter, $params){
                        foreach ($params['values'] as $value){
                            $query->orWhere($filter->field, 'LIKE', "%$value%");
                        }
                    });
                    break;
            }

        }

        return $query;
    }
}
