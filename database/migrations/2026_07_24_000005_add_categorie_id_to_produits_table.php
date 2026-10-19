<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->foreignId('categorie_id')->nullable()->after('description')->constrained()->nullOnDelete();
        });

        // Migrate existing string categories to IDs
        $categories = DB::table('categories')->pluck('id', 'nom');

        DB::table('produits')->whereNotNull('categorie')->chunkById(100, function ($produits) use ($categories) {
            foreach ($produits as $produit) {
                if (isset($categories[$produit->categorie])) {
                    DB::table('produits')->where('id', $produit->id)->update([
                        'categorie_id' => $categories[$produit->categorie],
                    ]);
                }
            }
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->string('categorie')->nullable()->after('description');
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');
        });
    }
};