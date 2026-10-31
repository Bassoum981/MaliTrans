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
        Schema::create('billets', function (Blueprint $table) {
            $table->id();
            $table->string('code_qr_hash')->unique();
            $table->foreignId('trajet_id')->constrained('trajets')->cascadeOnDelete();

            // Informations du passager et de la place
            $table->string('nom_passager');
            $table->string('telephone_passager')->nullable();
            $table->integer('numero_siege')->nullable();

            // Type de vente et traçabilité du guichetier
            $table->enum('type_vente', ['WEB', 'GUICHET']);
            $table->foreignId('vendu_par')->nullable()->constrained('users')->nullOnDelete(); // Guichetier concerné

            $table->decimal('prix', 10, 2);
            $table->enum('statut', ['PAYE', 'VALIDE', 'ANNULE'])->default('PAYE');
            $table->dateTime('date_validation')->nullable();
            $table->timestamps(); // Ajout des timestamps standards
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billets');
    }
};
