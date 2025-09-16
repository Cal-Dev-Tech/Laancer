<?php

namespace Modules\Subscription\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionType extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'validity', 'is_free'];

    protected static function newFactory()
    {
        return \Modules\Subscription\Database\factories\SubscriptionTypeFactory::new();
    }

    public static function all_types()
    {
        return self::select(['id', 'type', 'validity', 'is_free'])->get();
    }

    public function isFree(): bool
    {
        return (bool) $this->is_free;
    }

    public function subscriptions()
    {
        return $this->HasMany(Subscription::class,'subscription_type_id','id')->select(['id','subscription_type_id','title','logo','price','limit']);
    }
}
