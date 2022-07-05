<?php

namespace AND48\TableFilters\Traits;

use AND48\TableFilters\Models\Filter;
use http\Env\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
     * get scope for source data
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterSource($query) :Builder{
        return $query;
    }
}
