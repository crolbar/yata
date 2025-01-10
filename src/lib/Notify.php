<?php

namespace App\Lib;

use App\Util\Env;
use App\Models\NotifyModel;
Env::ParseEnv();


class Notify {
    private static function createJWT(string $tokenUri, string $clientEmail, string $privateKey): string
    {
        $header = base64_encode(
            json_encode(
                [
                    'alg' => 'RS256',
                    'typ' => 'JWT',
                ]
            )
        );

        $now = time();

        $payload = base64_encode(
            json_encode(
                [
                    'iss' => $clientEmail,
                    'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                    'aud' => $tokenUri,
                    'exp' => $now + 600, // 10 mins
                    'iat' => $now,
                ]
            )
        );

        $signature = '';

        openssl_sign(
            $header . '.' . $payload,
            $signature,
            $privateKey,
            'sha256'
        );

        $signature = base64_encode($signature);

        $jwt = $header . '.' . $payload . '.' . $signature;

        return $jwt;
    }

    private static function getAccessToken(): string
    {
        $clientEmail = getenv("client_email");
        $privateKey = json_decode(getenv("private_key"));
        $tokenUri = 'https://oauth2.googleapis.com/token';

        $jwt = Notify::createJWT($tokenUri, $clientEmail, $privateKey);


        $postData = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $tokenUri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            exit('Failed to fetch access token');
        }

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            return $data['access_token'];
        } else {
            exit('Error fetching access token: '.$data['error_description']);
            return null;
        }
    }

    public static function send_notif(string $title, string $text): void
    {
        $device_token = NotifyModel::get_fcm_device_token_for($_SESSION["id"]);
        if ($device_token === false) {
            return;
        }

        $access_token = Notify::getAccessToken();

        $url = 'https://fcm.googleapis.com/v1/projects/yata-43e21/messages:send';
        $headers = [
            "Authorization: Bearer $access_token",
            'content-type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $device_token,
                "data" => [
                    "title" => $title,
                    "text" => $text,
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
    }
}
