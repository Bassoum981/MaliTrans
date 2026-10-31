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
        Schema::create('trajets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->cascadeOnDelete();
            $table->foreignId('ligne_id')->constrained('lignes')->cascadeOnDelete();
            $table->foreignId('chauffeur_id')->constrained('users')->cascadeOnDelete(); // Liaison propre avec les users
            $table->dateTime('heure_depart_prevue');
            $table->dateTime('heure_depart_reelle')->nullable();
            $table->dateTime('heure_arrivee_reelle')->nullable();
            $table->enum('statut', ['PLANIFIE', 'EN_COURS', 'TERMINE', 'ANNULE'])->default('PLANIFIE');
            $table->timestamps(); // Ajout des timestamps pour le suivi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trajets');
    }
};
