<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class   Record extends Model
{
    //fillable fields
    use SoftDeletes;
    protected $fillable = [
        'record_id',
        'patient_id',
        'hospital_id',
        'assessment_date',
        'physician_name',
        'complement_by',
        'diagnosis',
        'treatment',
        'record_date',
        'medical_record_files',
    ];

    //relationship with hospital
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    //relationship with patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }


}
