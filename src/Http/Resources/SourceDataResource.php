<?php

namespace AND48\TableFilters\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SourceDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->{$this::getTableFilterSourceKeyName()},
            'name' => $this->name,
        ];
    }
}
