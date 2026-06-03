<?php
require 'vendor/autoload.php';

$url = 'https://maps.app.goo.gl/dPptyVH9aeSFVC7o9';
$client = new \GuzzleHttp\Client(['allow_redirects' => true]);
$response = $client->get($url);

$finalUrl = (string) $response->getBody()->getMetadata('uri');
echo "Final URL: " . $finalUrl . "\n";

if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
    echo "URL Match: " . $matches[1] . ", " . $matches[2] . "\n";
}

$body = (string) $response->getBody();
if (preg_match('/center=(-?\d+\.\d+)%2C(-?\d+\.\d+)/', $body, $metaMatches)) {
    echo "Body Match: " . $metaMatches[1] . ", " . $metaMatches[2] . "\n";
}

// Let's also check for INITIAL_DATA or meta tags that contain coordinates
if (preg_match('/meta content="https:\/\/maps\.google\.com\/maps\/api\/staticmap\?.*?center=(-?\d+\.\d+)%2C(-?\d+\.\d+)/', $body, $m)) {
    echo "Meta Content: " . $m[1] . ", " . $m[2] . "\n";
}

if (preg_match('/window\.APP_INITIALIZATION_STATE=\[\[\[.*?(-?\d+\.\d+),(-?\d+\.\d+)/', $body, $m)) {
    echo "APP_INIT: " . $m[1] . ", " . $m[2] . "\n";
}

// Another possible pattern in body
if (preg_match('/\[\[\[(\d+\.\d+),(-?\d+\.\d+)\]/', $body, $m)) {
    echo "Generic Pattern: " . $m[2] . ", " . $m[1] . "\n"; // [ [ [ lng, lat ]
}
