<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model
{
    //fillable fields
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'medchain_id',
        'name',
        'address',
        'phone',
        'image',
    ];

    //relationship with hospital license
    public function license()
    {
        return $this->hasOne(HospitalLicense::class);
    }

    //relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
