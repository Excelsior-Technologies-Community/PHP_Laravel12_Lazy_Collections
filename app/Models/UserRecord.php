<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRecord extends Model
{
    protected $table = 'lazy_user_records';

    protected $fillable = [
        'name',
        'email',
    ];
}
