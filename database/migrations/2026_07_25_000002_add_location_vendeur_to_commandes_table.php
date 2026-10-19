<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('adresse_vendeur')->nullable()->after('longitude');
            $table->decimal('latitude_vendeur', 10, 7)->nullable()->after('adresse_vendeur');
            $table->decimal('longitude_vendeur', 10, 7)->nullable()->after('latitude_vendeur');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['adresse_vendeur', 'latitude_vendeur', 'longitude_vendeur']);
        });
    }
};
