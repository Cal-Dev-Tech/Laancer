<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanContactFreelancer extends Model
{
    use HasFactory;
    
    
    protected $fillable = [
        'can_contact_freelancer',
        'show_contact_me_before_login',
    ];
}
