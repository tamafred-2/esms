<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batch_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('course_batches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('registration_status')->nullable();
            $table->string('delivery_mode')->nullable();
            $table->string('provider_type')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('congressional_district')->nullable();
            $table->string('municipality')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_enrollments');
    }
};
