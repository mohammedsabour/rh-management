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
        Schema::create('emplacements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 20)->nullable()->unique();
            $table->string('type', 20)->default('bureau'); // batiment | etage | bureau | salle | magasin | autre
            $table->foreignId('parent_id')->nullable()
                ->constrained('emplacements')->nullOnDelete(); // ex: Bâtiment A > Étage 1 > Bureau 12
            $table->foreignId('departement_id')->nullable()
                ->constrained('departements')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emplacements');
    }
};
