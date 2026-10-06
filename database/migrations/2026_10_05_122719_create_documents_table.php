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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->default('autre')->index();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_extension', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
