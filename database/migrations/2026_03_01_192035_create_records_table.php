<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            //record_id
            $table->string('record_id')->unique();
            //patient_id
            $table->unsignedBigInteger('patient_id');
            //hospital_id
            $table->unsignedBigInteger('hospital_id');

            //Date of Assessment
            $table->date('assessment_date')->nullable();
            //Physician’s Name
            $table->string('physician_name')->nullable();
            //Complement By
            $table->string('complement_by')->nullable();
            //diagnosis
            $table->text('diagnosis')->nullable();
            //treatment
            $table->text('treatment')->nullable();
            //record_date
            $table->date('record_date')->nullable();
            //Medical Record Files (file path)
            $table->string('medical_record_files')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
