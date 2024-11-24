<?php

require "src/util.php";
use App\Util\Env;
Env::ParseEnv();

$ch = curl_init();
$key = getenv('GT');

$url = 'https://fcm.googleapis.com/v1/projects/yata-43e21/messages:send';
$headers = [
    "Authorization: Bearer $key",
    'content-type: application/json'
];

$data = [
    "message" => [
        "token" => "ebnURHwVQWqYWiZhdROdIu:APA91bFrlotv-_zNsCEP5LPyZPvg8ODJXCH4UWSOS-X3vFyKLtvziOxWc56y1-DbibA4gxW4uEGRU8Vh4lXIzwsIJwyrYUWZK4ALaRKXVX2WOVVw6d6fliE",
        "data" => [
            "titile" => "yo",
        ],
    ]
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
