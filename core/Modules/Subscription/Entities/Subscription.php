<?php

namespace Modules\Subscription\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_type_id',
        'title',
        'logo',
        'price',
        'limit',
        'status',
        'commission_rate',
        'commission_type'
    ];

    protected $casts = ['status' => 'integer'];

    protected static function newFactory()
    {
        return \Modules\Subscription\Database\factories\SubscriptionFactory::new();
    }

    public function features()
    {
        return $this->hasMany(SubscriptionFeature::class, 'subscription_id', 'id');
    }

    public function subscription_type()
    {
        return $this->belongsTo(SubscriptionType::class, 'subscription_type_id', 'id');
    }

    public function user_subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'subscription_id', 'id');
    }


    public function getEffectiveCommissionRate()
    {
        if ($this->commission_rate !== null && $this->commission_type !== null) {
            return [
                'rate' => $this->commission_rate,
                'type' => $this->commission_type
            ];
        }

        // Fallback to global settings
        return [
            'rate' => get_static_option('admin_commission_charge') ?? 25,
            'type' => get_static_option('admin_commission_type') ?? 'percentage'
        ];
    }

    public function hasCustomCommission()
    {
        return $this->commission_rate !== null && $this->commission_type !== null;
    }
}
