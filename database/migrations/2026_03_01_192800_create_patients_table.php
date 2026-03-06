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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            //MedChain ID
            //user_id
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('medchain_id')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            //gender
            $table->string('gender')->nullable();
            //Height
            $table->float('height')->nullable();
            //Weight
            $table->float('weight')->nullable();
            //Birth Date
            $table->date('birth_date')->nullable();
            //Contact Info
            $table->string('contact')->nullable();
            //Place of Birth
            $table->string('place_of_birth')->nullable();

            //EmergencyContact
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_contact')->nullable();

            $table->string('image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
