<?php


require "src/util.php";
use App\Util\Env;
Env::ParseEnv();

$ch = curl_init();
$os = getenv('ONESIGNAL');

$url = 'https://api.onesignal.com/notifications';
$headers = [
    "Authorization: Basic $os",
    'accept: application/json',
    'content-type: application/json'
];

$data = [
    'app_id' => '8fb119b2-c4da-4a33-95c4-44e8ea21697a',
    'target_channel' => 'push',
    'contents' => [
        'en' => 'English Message',
    ],
    'include_aliases' => [
        'onesignal_id' => ['82aafd02-277d-4ff3-aa6f-8c82dddfba44']
    ]
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
