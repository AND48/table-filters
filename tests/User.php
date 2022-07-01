<?php

namespace AND48\TableFilters\Tests;

use AND48\TableFilters\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use Filterable, HasFactory;

    const STATUS_NEW = 'new';
    const STATUSES = [self::STATUS_NEW, 'verified', 'active', 'suspended'];

    protected static function newFactory(){
        return UserFactory::new();
    }

    public function parent(){
        return $this->belongsTo(self::class);
    }
}
