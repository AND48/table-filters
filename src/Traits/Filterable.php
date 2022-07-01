<?php

namespace AND48\TableFilters\Traits;

use AND48\TableFilters\Models\Filter;

trait Filterable
{
    protected static function getFilterModel(){
        return self::class;
    }

    public static function filterList($load_operators = false){
        $filters = Filter::where('model', self::getFilterModel())->get();
        if ($load_operators){
            $filters->transform(function ($filter){
                $filter->operators = config('filters.operators.'.$filter['type']);
                return $filter;
            });
        }
        return $filters;
    }

    public function addFilter($filter){
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

    public function addFilters($filters){
        foreach ($filters as $filter){
            self::addFilter($filter);
        }
    }
}
