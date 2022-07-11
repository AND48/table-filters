<?php

namespace AND48\TableFilters\Tests;

use AND48\TableFilters\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class User extends Model
{
    use Filterable, HasFactory;

    protected $fillable = ['parent_id'];

    protected $dates = ['birthday'];

    const STATUS_NEW = 'new';
    const STATUS_VERIFIED = 'verified';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUSES = [self::STATUS_NEW, self::STATUS_VERIFIED, self::STATUS_ACTIVE, self::STATUS_SUSPENDED];

    protected static function newFactory(){
        return UserFactory::new();
    }

    public function parent(){
        return $this->belongsTo(self::class);
    }

    public function getParentUserNameAttribute(){
        return $item->parent->name ?? '';
    }

    public static function getFilterSourceLoad(){
        return ['parent'];
    }

    public function scopeFilterSource($query){
        return $query->where('is_blocked', false);
    }

    public static function getFilterSourceField() :string{
        return 'email';
    }
}
