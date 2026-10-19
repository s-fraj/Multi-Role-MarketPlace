<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boutique_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('total', 10, 2);
            $table->decimal('frais_livraison', 10, 2)->default(7);
            $table->enum('statut', ['en_attente', 'en_cours', 'confirmee', 'annulee'])->default('en_attente');
            $table->string('nom_client');
            $table->string('telephone');
            $table->string('adresse');
            $table->string('ville');
            $table->string('code_postal');
            $table->boolean('vendeur_confirme')->default(false);
            $table->boolean('client_confirme')->default(false);
            $table->timestamp('vendeur_confirme_at')->nullable();
            $table->timestamp('client_confirme_at')->nullable();
            $table->text('raison_annulation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};