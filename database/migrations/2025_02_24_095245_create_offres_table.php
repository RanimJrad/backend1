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
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('departement');
            $table->string('poste');
            $table->text('description');
            $table->date('datePublication')->default(now()); // Date du jour par défaut
            $table->date('dateExpiration');
            $table->boolean('valider')->default(false); // Ajout du champ valider (false par défaut)
            $table->timestamps();
        });
    }  
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
