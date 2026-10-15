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
        Schema::create('categorie_equipements', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code', 5)->unique(); // préfixe du code-barres (ex: INF, MOB)
            $table->string('numero_compte', 10)->index();
            $table->foreignId('parent_id')->nullable()
                ->constrained('categorie_equipements')->nullOnDelete(); // ex: Informatique > Ordinateur portable
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorie_equipements');
    }
};
