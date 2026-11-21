<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Président + deux membres, tous employés de l'application
        Schema::create('commission_membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('commissions_inventaire')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 10)->default('membre');                // president | membre
            $table->timestamps();

            $table->unique(['commission_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_membres');
    }
};