<?php

namespace AND48\TableFilters\Tests;

use AND48\TableFilters\Traits\TableFilterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    use TableFilterable, HasFactory;

    protected $fillable = ['parent_id', 'role', 'balance'];

    protected $dates = ['birthday'];

    const STATUS_NEW = 'new';
    const STATUS_VERIFIED = 'verified';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUSES = [self::STATUS_NEW, self::STATUS_VERIFIED, self::STATUS_ACTIVE, self::STATUS_SUSPENDED];

    const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';
    const ROLES = [self::ROLE_ADMIN, self::ROLE_MANAGER];

    protected static function newFactory(){
        return UserFactory::new();
    }

    public function parent(){
        return $this->belongsTo(self::class);
    }

    public function children(){
        return $this->hasMany(self::class,'id','parent_id');
    }

    public function getParentUserNameAttribute(){
        return $item->parent->name ?? '';
    }

    public static function getTableFilterSourceLoad(){
        return ['parent'];
    }

    public function scopeTableFilterSource($query){
        return $query->where('is_blocked', false);
    }

    public function scopeManager($query){
        return $query->where('role', self::ROLE_MANAGER);
    }

    public static function getTableFilterSourceField() :string{
//        return 'email';
        return '(users.email || " " || users.name)';
    }

    public function halfBalanceTableFilterable(){
        return 'CAST(users.balance/2 AS FLOAT)';
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
