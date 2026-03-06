<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCredential extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
