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
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom');

            $table->date('date_debut');
            $table->date('date_fin')->nullable();

            $table->enum('statut', ['ouvert', 'cloture'])
                ->default('ouvert');

            $table->foreignId('emplacement_id')->nullable()->constrained('emplacements')->nullOnDelete();

            $table->foreignId('cree_par')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index(['date_debut', 'date_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaires');
    }
};
