<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_vendeurs', function (Blueprint $table) {
            $table->foreignId('commande_id')->nullable()->constrained()->nullOnDelete()->after('produit_id');
        });

        Schema::table('notification_clients', function (Blueprint $table) {
            $table->foreignId('commande_id')->nullable()->constrained()->nullOnDelete()->after('produit_id');
        });
    }

    public function down(): void
    {
        Schema::table('notification_vendeurs', function (Blueprint $table) {
            $table->dropColumn('commande_id');
        });
        Schema::table('notification_clients', function (Blueprint $table) {
            $table->dropColumn('commande_id');
        });
    }
};