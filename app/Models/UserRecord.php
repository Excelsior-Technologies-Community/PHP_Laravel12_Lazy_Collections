<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRecord extends Model
{
    use HasFactory; 
    protected $table = 'lazy_user_records';

    protected $fillable = [
        'name',
        'email',
    ];
}
