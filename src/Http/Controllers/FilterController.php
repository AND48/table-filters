<?php

namespace AND48\TableFilters\Http\Controllers;

use AND48\TableFilters\Http\Resources\SourceDataResource;
use AND48\TableFilters\Models\Filter;

class FilterController extends Controller
{
    public function sourceData()
    {
        //
        $filter = Filter::findOrFail(request('filter_id'));
        $data = $filter->sourceData(request()->input('page', 1), request()->input('query'));
        $load_more = ($data->count() >= config('filters.source_data_per_page'));
        return SourceDataResource::collection($data)->additional(['meta' =>['loadMore' => $load_more]]);
    }
}

