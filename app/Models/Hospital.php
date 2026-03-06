<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model
{
    //fillable fields
    use SoftDeletes;
    protected $fillable = [
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
}
