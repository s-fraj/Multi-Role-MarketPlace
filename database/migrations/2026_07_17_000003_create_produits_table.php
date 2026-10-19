<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('categorie')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('prix', 10, 2);
            $table->decimal('prix_barre', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('poids', 6, 2)->nullable();
            $table->json('variantes')->nullable();
            $table->boolean('publie')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
