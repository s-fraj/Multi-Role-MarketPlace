<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->string('banque')->nullable()->after('statut');
            $table->string('titulaire_compte')->nullable()->after('banque');
            $table->string('rib')->nullable()->after('titulaire_compte');
            $table->boolean('rib_verifie')->default(false)->after('rib');
        });
    }

    public function down(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropColumn(['banque', 'titulaire_compte', 'rib', 'rib_verifie']);
        });
    }
};
