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
            $table->foreignId('bus_id')->constrained('buses');
            $table->unsignedBigInteger('chauffeur_id');
            $table->foreignId('ligne_id')->constrained('lignes');
            $table->dateTime('heure_depart_prevue');
            $table->dateTime('heure_depart_reelle')->nullable();
            $table->dateTime('heure_arrivee_reelle')->nullable();
            $table->enum('statut', ['PLANIFIE', 'EN_COURS', 'TERMINE', 'ANNULE'])->default('PLANIFIE');
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