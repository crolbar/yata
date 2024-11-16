<?php

namespace App\Lib;

use App\Lib\GoogleJWK;
use App\Util\Env;

function base64UrlDecode(string $input): string|bool
{
    return base64_decode(strtr($input, '-_', '+/'));
}

class GoogleJWT
{
    public static function getJWTFromRefresh(string $refresh_token): mixed
    {
        Env::ParseEnv();

        $google_oauth_client_id     = getenv('GOATH_CLIENT_ID');
        $google_oauth_client_secret = getenv('GOATH_CLIENT_SECRET');
        $google_oauth_redirect_uri  = getenv('GOATH_REDIRECT');
        $token_uri                  = 'https://accounts.google.com/o/oauth2/token';

        $data = [
            'client_id'     => $google_oauth_client_id,
            'client_secret' => $google_oauth_client_secret,
            'redirect_uri'  => $google_oauth_redirect_uri,
            'refresh_token' => $refresh_token,
            'grant_type'    => 'refresh_token'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $token_uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        $id_token = $response["id_token"];

        if (self::verifyJwtSignature($id_token) === false) {
            echo 'Invalid id_token.';
            exit;
        }

        return $id_token;
    }

    public static function getTokens(string $code): mixed
    {
        Env::ParseEnv();

        $google_oauth_client_id     = getenv('GOATH_CLIENT_ID');
        $google_oauth_client_secret = getenv('GOATH_CLIENT_SECRET');
        $google_oauth_redirect_uri  = getenv('GOATH_REDIRECT');
        $token_uri                  = 'https://accounts.google.com/o/oauth2/token';

        $data = [
            'client_id'     => $google_oauth_client_id,
            'client_secret' => $google_oauth_client_secret,
            'redirect_uri'  => $google_oauth_redirect_uri,
            'code'          => $code,
            'grant_type'    => 'authorization_code'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $token_uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);

        if (!isset($response['access_token'])) {
            echo 'Invalid access token! Please try again later!';
            exit;
        }

        $id_token = $response["id_token"];

        if (self::verifyJwtSignature($id_token) === false) {
            echo 'Invalid id_token.';
            exit;
        }

        return $response;
    }

    public static function getUserInfo(string $access_token): mixed
    {
        $http_header    = ['Authorization: Bearer ' . $access_token];
        $url            = 'https://www.googleapis.com/oauth2/v3/userinfo';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $user_info = json_decode($response, true);

        if (!isset($user_info['email'])) {
            echo 'Could not retrieve profile information! Please try again later!';
            exit;
        }

        return $user_info;
    }

    public static function verifyJwtSignature(string $id_token): string|bool
    {
        if (empty($id_token) || substr_count($id_token, '.') !== 2) {
            echo "Invalid token format.";
            return false;
        }

        list($header, $payload, $signature) = explode('.', $id_token);


        $decoded_signature = base64UrlDecode($signature);
        if ($decoded_signature === false) {
            echo "Could not decode signature.";
            return false;
        }

        $decoded_payload = base64UrlDecode($payload);
        if ($decoded_payload === false) {
            echo "Could not decode payload.";
            return false;
        }

        $decoded_header = base64UrlDecode($header);
        if ($decoded_header === false) {
            echo "Could not decode header.";
            return false;
        }

        $decoded_header = json_decode($decoded_header, true);
        $public_key = GoogleJWK::getPublicKey($decoded_header['kid']);


        $result = openssl_verify(
            $header . '.' . $payload,
            $decoded_signature,
            $public_key,
            OPENSSL_ALGO_SHA256
        );

        if ($result === false || $result === 0) {
            echo "Signature is invalid.";
            return false;
        }

        if ($result === -1) {
            echo "Error in signature verification.";
            return false;
        }

        return $decoded_payload;
    }
}
