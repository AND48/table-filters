<?php

namespace AND48\TableFilters\Traits;

use AND48\TableFilters\Models\Filter;
use http\Env\Url;
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
    public static function getFilterSourceField(){
        return 'name';
    }

    /**
     *
     * get sorting field for source data
     *
     * @return string
     */
    public static function getFilterSourceOrderBy(){
        return (new static())->getKeyName();
    }

    /**
     *
     * get field count per page for source data
     *
     * @return int
     */
    public static function getFilterSourcePerPage(){
        return 10;
    }

    /**
     *
     * get lazy load array for source data
     *
     * @return array
     */
    public static function getFilterSourceLoad(){
        return [];
    }

    /**
     *
     * get transform method for source data
     *
     * @return Collection
     */
    public static function getFilterSourceTransform($item){
        return $item->only([self::getKeyName(), self::getFilterSourceField()]);
    }
}
