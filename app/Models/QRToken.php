<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QRToken extends Model
{
    //table name qr_tokens
    protected $table = 'qr_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'approved_at',
        'is_used',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
