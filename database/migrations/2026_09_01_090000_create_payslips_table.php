<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('month'); // first day of the payroll month
            $table->decimal('base_salary', 10, 2);
            $table->decimal('adjustments', 10, 2)->default(0); // + bonus, - deduction
            $table->string('adjustment_notes')->nullable();
            $table->decimal('net_amount', 10, 2);
            $table->string('status')->default('draft'); // draft, paid
            $table->date('paid_date')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};