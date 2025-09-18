<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'create_project_id',
        'type',
        'check_numeric_title',
        'basic_check_numeric',
        'standard_check_numeric',
        'premium_check_numeric',

        'basic_extra_price',
        'standard_extra_price',
        'premium_extra_price',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'integer',
        'basic_extra_price' => 'decimal:2',
        'standard_extra_price' => 'decimal:2',
        'premium_extra_price' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'create_project_id', 'id');
    }
}
