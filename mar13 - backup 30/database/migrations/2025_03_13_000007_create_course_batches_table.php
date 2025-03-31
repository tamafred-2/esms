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
        Schema::create('course_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('batch_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_students');
            $table->time('morning_time_in')->nullable();
            $table->time('morning_time_out')->nullable();
            $table->time('afternoon_time_in')->nullable();
            $table->time('afternoon_time_out')->nullable();
            $table->string('status')->default('upcoming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_batches');
    }
};
