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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->date('date_absence');
            $table->enum('type', ['maladie', 'congé', 'personnel'])->default('maladie');
            $table->foreignId('employe_id')->constrained('users')->onDelete('cascade');
            $table->enum('justificatif', ['oui', 'non'])->default('non');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
