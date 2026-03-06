<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HospitalLicense extends Model
{
    //fillable fields
    use SoftDeletes;
    protected $fillable = [
        'hospital_id',
        'license_number',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'license_document',
    ];

    //relationship with hospital
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
