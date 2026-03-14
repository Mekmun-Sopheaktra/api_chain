<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    //fillable fields
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'medchain_id',
        'first_name',
        'last_name',
        'gender',
        'height',
        'weight',
        'birth_date',
        'contact',
        'place_of_birth',

        'emergency_name',
        'emergency_relationship',
        'emergency_contact',
        'image',
    ];

    //relationship with records
    public function records()
    {
        return $this->hasMany(Record::class);
    }

    //relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
