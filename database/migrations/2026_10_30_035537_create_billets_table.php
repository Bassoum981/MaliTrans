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
            $table->enum('type_vente', ['WEB', 'GUICHET']);
            $table->decimal('prix', 10, 2);
            $table->enum('statut', ['PAYE', 'VALIDE', 'ANNULE'])->default('PAYE');
            $table->dateTime('date_validation')->nullable();
            $table->timestamp('created_at')->useCurrent();
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
