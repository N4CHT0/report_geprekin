<?php
require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client(['allow_redirects' => true]);
$response = $client->get('https://maps.app.goo.gl/91dcLsVdj4GcDSzw6');

$finalUrl = $response->getBody()->getMetadata('uri');
if (!$finalUrl && method_exists($response, 'getEffectiveUrl')) {
    $finalUrl = $response->getEffectiveUrl();
}

echo "Final URL: " . $finalUrl . "\n";

// Alternatively, let's use standard cURL which is sometimes easier for checking effective URLs.
$ch = curl_init('https://maps.app.goo.gl/91dcLsVdj4GcDSzw6');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "CURL Final URL: " . $url . "\n";
