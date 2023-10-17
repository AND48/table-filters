<?php

namespace AND48\TableFilters\Http\Controllers;

use AND48\TableFilters\Http\Resources\FilterStorageResource;
use AND48\TableFilters\Models\Filter;
use AND48\TableFilters\Models\FilterStorage;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class FilterStorageController extends Controller
{
    public function index()
    {
        $user = $this->getAuthUser();
        $filter = Filter::find(intval(request()->input('filter_id')));
        if (!$filter){
            return FilterStorageResource::collection(collect());
        }
        $storages = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('model', $filter->model)
            ->where(function($query) use ($user){
                $query->whereNull('causer_id')->orWhere('causer_id', $user->id);
        })->get();
        return FilterStorageResource::collection($storages);
    }

    public function show($id)
    {
        $user = $this->getAuthUser();
        $storage = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where(function($query) use ($user){
                $query->whereNull('causer_id')->orWhere('causer_id', $user->id);
            })->findOrFail($id);
        return $this->response($storage);
    }

    public function edit($id)
    {
        $user = $this->getAuthUser();
        $storage = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->findOrFail($id);
        return $this->response($storage);
    }

    public function store()
    {
        $this->makeValidation();
        $request_filters = request()->input('rules');
        $filter = Filter::find(Arr::first($request_filters['filters'] ?? [])['id'] ?? null);
        if (!$filter){
            abort(403);
        }
        $user = $this->getAuthUser();
        $storage = FilterStorage::create([
                'name' => request()->input('name'),
                'model' => $filter->model,
                'rules' => request()->input('rules'),
                'causer_type' => $user->getMorphClass(),
                'causer_id' => $user->id
            ]);
        return $this->response($storage);

    }

    public function update($id)
    {
        $this->makeValidation();
        $user = $this->getAuthUser();
        $storage = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->findOrFail($id);
        $storage->update([
                'name' => request()->input('name'),
                'rules' => request()->input('rules'),
        ]);
        return $this->response($storage);
    }

    public function destroy($id)
    {
        $user = $this->getAuthUser();
        $storage = FilterStorage::where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->findOrFail($id);
        $storage->delete();
        return $this->response($storage);
    }

    protected function response($storage){
        return new FilterStorageResource($storage);
    }

    protected function getGuard(){
        return null;
    }

    protected function getAuthUser(){
        //reload it for multiple guards
        if ($guard = $this->getGuard()){
            $user = Auth::guard($guard)->user();
        } else {
            $user = Auth::user();
        }
        if (!$user){
            abort(403);
        }
        return $user;
    }

    protected function makeValidation(){
        request()->validate([
            'name' => 'required|max:255',
            'rules' => 'required|array',
            'rules.filters' => 'required|array',
            'rules.filters.*' => 'required|array',
            'rules.filters.*.id' => 'required|integer|min:1',
            'rules.filters.*.operator' => 'required|max:2',
            'rules.filters.*.values' => 'required',
        ]);
    }
}

