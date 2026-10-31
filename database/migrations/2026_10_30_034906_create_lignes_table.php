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
        Schema::create('lignes', function (Blueprint $table) {
            $table->id();
            $table->string('ville_depart');
            $table->string('ville_arrivee');
            $table->decimal('distance_km', 8, 2)->nullable(); // Ex: 350.50 km
            $table->decimal('prix_base', 10, 2);               // Prix indicatif du ticket sur cette ligne
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lignes');
    }
};
