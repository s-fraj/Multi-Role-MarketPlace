<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('client', 'vendeur', 'admin') DEFAULT 'client'");

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('est_bloque')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('est_bloque');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('client', 'vendeur') DEFAULT 'client'");
    }
};
