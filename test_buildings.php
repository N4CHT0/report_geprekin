<?php

$lat = -7.4555838;
$lng = 112.7066232;

$query = <<<EOF
[out:json][timeout:25];
(
  node["building"](around:1000,$lat,$lng);
  way["building"](around:1000,$lat,$lng);
);
out count;
EOF;

$ch = curl_init('https://overpass-api.de/api/interpreter');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . urlencode($query));
$response = curl_exec($ch);
curl_close($ch);

echo $response;
