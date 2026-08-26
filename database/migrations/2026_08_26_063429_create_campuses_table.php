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
    Schema::create('campuses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
        $table->string('code')->unique();
        $table->string('name');
        $table->string('address')->nullable();
        $table->string('city')->nullable();
        $table->string('region')->nullable();
        $table->string('contact_phone')->nullable();
        $table->string('contact_email')->nullable();
        $table->string('timezone')->default('UTC');
        $table->string('currency', 3)->default('USD');
        $table->string('academic_year')->nullable();
        $table->string('grading_scale')->nullable();
        $table->string('pay_cycle')->default('monthly');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
