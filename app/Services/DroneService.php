<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * The only class in this codebase that talks to the Python drone service.
 * Laravel never touches MAVLink — it only ever calls this Flask API.
 *
 * Reads from config/drone.php (which itself reads .env), not from env()
 * directly — see that file for why. Set these in .env:
 *   DRONE_SERVICE_URL=http://127.0.0.1:5000
 *   DRONE_SERVICE_API_KEY=change-me   (must match DRONE_API_KEY in server.py)
 *
 * If you run `php artisan config:cache`, run it again after changing
 * either of those .env values, or the cached copy will keep being used.
 */
class DroneService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('drone.base_url', 'http://127.0.0.1:5000'), '/');
        $this->apiKey = config('drone.api_key', 'change-me');
    }

    /**
     * POST /start-delivery — kicks off a new mission. Returns the decoded
     * JSON body (contains "mission" with mission_id, status, etc.).
     *
     * @throws \RuntimeException on network failure or a non-2xx response.
     */
    public function demarrerLivraison(array $pickup, array $dropoff, int $commandeId): array
    {
        $response = $this->client()
            ->post("{$this->baseUrl}/start-delivery", [
                'pickup' => $pickup,
                'dropoff' => $dropoff,
                'commande_id' => $commandeId,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Drone service error ({$response->status()}): {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /mission-status/{missionId} — current status of one mission.
     *
     * @throws \RuntimeException on network failure or a non-2xx response.
     */
    public function statutMission(string $missionId): array
    {
        $response = $this->client()->get("{$this->baseUrl}/mission-status/{$missionId}");

        if (! $response->successful()) {
            throw new \RuntimeException("Drone service error ({$response->status()}): {$response->body()}");
        }

        return $response->json();
    }

    /**
     * GET /fleet-status — state of all 3 drones + the current queue.
     *
     * @throws \RuntimeException on network failure or a non-2xx response.
     */
    public function statutFlotte(): array
    {
        $response = $this->client()->get("{$this->baseUrl}/fleet-status");

        if (! $response->successful()) {
            throw new \RuntimeException("Drone service error ({$response->status()}): {$response->body()}");
        }

        return $response->json();
    }

    private function client()
    {
        return Http::withHeaders(['X-API-Key' => $this->apiKey])->timeout(5);
    }
}
