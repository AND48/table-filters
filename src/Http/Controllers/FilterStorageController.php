<?php

namespace AND48\TableFilters\Http\Controllers;

use AND48\TableFilters\Http\Resources\FilterStorageResource;
use AND48\TableFilters\Models\FilterStorage;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FilterStorageController extends Controller
{
    public function index()
    {
        $user = $this->getAuthUser();
        $filters = FilterStorage::whereNull('causer_id')->orWhere(function($query) use ($user){
            $query->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id);
        })->get();
        return FilterStorageResource::collection($filters);
    }

    public function show($id)
    {
        $user = $this->getAuthUser();
        $filter = FilterStorage::whereNull('causer_id')->orWhere(function($query) use ($user){
            $query->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id);
        })->findOrFail($id);
        return $this->response($filter);
    }

    public function edit($id)
    {
        $user = $this->getAuthUser();
        $filter = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->findOrFail($id);
        return $this->response($filter);
    }

    public function store()
    {
        $user = $this->getAuthUser();
        $filter = FilterStorage::create([
                'name' => request()->input('name'),
                'filters' => request()->input('filters'),
                'causer_type' => $user->getMorphClass(),
                'causer_id' => $user->id
            ]);
        return $this->response($filter);

    }

    public function update($id)
    {
        $user = $this->getAuthUser();
        $filter = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->findOrFail($id);
        $filter->update([
                'name' => request()->input('name'),
                'filters' => request()->input('filters'),
        ]);
        return $this->response($filter);
    }

    public function destroy($id)
    {
        $user = $this->getAuthUser();
        $filter = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->findOrFail($id);
        $filter->delete();
        return response();
    }

    protected function response($filter){
        return new FilterStorageResource($filter);
    }

    protected function getAuthUser(){
        //reload it for multiple guards
        $user = Auth::user();
        if (!$user){
            abort(403);
        }
        return $user;
    }
}

