<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            if (!Schema::hasTable('school_user')) {
                Schema::create('school_user', function (Blueprint $table) {
                    $table->id();
                    
                    // Create the columns
                    $table->foreignId('school_id')->constrained()->onDelete('cascade');
                    $table->foreignId('user_id')->constrained()->onDelete('cascade');
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();

                    // Add unique constraint
                    $table->unique(['school_id', 'user_id']);
                });
            }
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down()
    {
        Schema::dropIfExists('school_user');
    }
};
