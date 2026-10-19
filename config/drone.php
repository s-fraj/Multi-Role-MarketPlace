<?php

$wslIp = env('DRONE_SERVICE_URL');

// Si aucune URL fixe n'est définie, tente de découvrir l'IP WSL
if (empty($wslIp) && PHP_OS_FAMILY === 'Windows') {
    $output = shell_exec('wsl hostname -I 2>nul');
    if ($output) {
        $ips = explode(' ', trim($output));
        $wslIp = 'http://' . $ips[0] . ':5000';
    }
}

return [
    'base_url' => rtrim($wslIp ?: 'http://127.0.0.1:5000', '/'),
    'api_key' => env('DRONE_SERVICE_API_KEY', '1f2r3t5g4u4yt132154'),
];

