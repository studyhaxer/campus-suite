<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable(); // male, female, other
            $table->date('admission_date');
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->unique(['campus_id', 'admission_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};