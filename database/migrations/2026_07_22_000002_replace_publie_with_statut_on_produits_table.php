<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            // brouillon | en_attente | en_ligne | refuse
            $table->string('statut')->default('brouillon')->after('variantes');
            $table->text('raison_refus')->nullable()->after('statut');
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn('publie');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->boolean('publie')->default(false)->after('variantes');
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['statut', 'raison_refus']);
        });
    }
};
