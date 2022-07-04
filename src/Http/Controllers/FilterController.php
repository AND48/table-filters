<?php

namespace AND48\TableFilters\Http\Controllers;

use AND48\TableFilters\Models\Filter;

class FilterController extends Controller
{
    public function sourceData()
    {
        //
        $filter = Filter::findOrFail(request('filter_id'));

        return response()->json($filter->sourceData(
            request()->input('page', 1), request()->input('query')));
    }
}

