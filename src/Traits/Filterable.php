<?php

namespace AND48\TableFilters\Traits;

use AND48\TableFilters\Models\Filter;

trait Filterable
{
    protected static function getFilterModel(){
        return self::class;
    }

    protected static function getFilterResponseFields(){
        return ['id','type','caption','operators'];
    }

    public static function filterList($load_operators = true){
        $filters = Filter::where('model', self::getFilterModel())->get();
        if ($load_operators){
            $filters->transform(function ($filter){
                $filter->operators = config('filters.operators.'.$filter['type']);
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
}
