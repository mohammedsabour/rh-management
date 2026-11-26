<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Commission A ou B d'un récolement
        Schema::create('commissions_inventaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->string('code', 1);                                    // A | B
            $table->string('statut', 20)->default('en_cours');            // en_cours | terminee
            $table->dateTime('debut_le')->nullable();                     // premier pointage
            $table->dateTime('termine_le')->nullable();
            $table->unsignedInteger('nb_non_numerotes')->nullable();      // « خلاصة اللجنة »
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['inventaire_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions_inventaire');
    }
};