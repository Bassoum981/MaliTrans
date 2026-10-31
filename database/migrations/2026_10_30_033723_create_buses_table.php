<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('immatriculation', 20)->unique();
            $table->integer('capacite');
            $table->enum('statut', ['EN_SERVICE', 'EN_MAINTENANCE', 'HORS_SERVICE'])->default('EN_SERVICE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
