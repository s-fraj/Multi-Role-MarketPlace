<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraisons_drone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->unique()->constrained('commandes')->cascadeOnDelete();

            $table->string('mission_id')->nullable()->unique();
            $table->unsignedTinyInteger('drone_id')->nullable();

            // Mirrors the Python service's status strings exactly (Waiting
            // for drone / Taking off / Going to pickup / Package picked /
            // Going to client / Delivered / Completed / Error), plus one
            // Laravel-only value: "Envoi impossible" when the initial call
            // to the drone service itself failed (service unreachable).
            $table->string('statut')->nullable();
            $table->unsignedInteger('queue_position')->nullable();
            $table->text('erreur')->nullable();

            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->decimal('dropoff_lat', 10, 7);
            $table->decimal('dropoff_lng', 10, 7);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraisons_drone');
    }
};
