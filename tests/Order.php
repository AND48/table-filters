<?php

namespace AND48\TableFilters\Tests;

use AND48\TableFilters\Traits\TableFilterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use TableFilterable, HasFactory;

    protected $fillable = ['name', 'price', 'status', 'user_id'];

    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_CLOSED = 'closed';
    const STATUSES = [self::STATUS_NEW, self::STATUS_PENDING, self::STATUS_CLOSED];

    protected static function newFactory(){
        return OrderFactory::new();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
