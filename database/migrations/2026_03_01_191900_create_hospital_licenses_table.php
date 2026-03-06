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
        Schema::create('hospital_licenses', function (Blueprint $table) {
            $table->id();
            //hospital_id
            $table->unsignedBigInteger('hospital_id');
            //license_number
            $table->string('license_number')->unique();
            //issue_date
            $table->date('issue_date')->nullable();
            //expiry_date
            $table->date('expiry_date')->nullable();
            //issuing_authority
            $table->string('issuing_authority')->nullable();
            //license_document (file path)
            $table->string('license_document')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_licenses');
    }
};
