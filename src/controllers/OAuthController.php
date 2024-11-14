<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Util\Env;

class OAuthController
{
    public static function login(): void
    {
        Router::view('login');
    }

    public static function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /');
    }

    public static function profile(): void
    {
        Router::view('profile');
    }

    public static function googleOAth(): void
    {
        Env::ParseEnv();

        $client_id      = getenv('GOATH_CLIENT_ID');
        $redirect_uri   = getenv('GOATH_REDIRECT');
        $response_type  = 'code';
        $scope          = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';

        $google_oauth_uri = "https://accounts.google.com/o/oauth2/v2/auth";

        $params = [
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => $response_type,
            'scope'         => $scope,
        ];

        header('Location: ' . $google_oauth_uri . '?' . http_build_query($params));
    }

    public static function googleRedirect(): void
    {
        session_start();

        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            echo "got response with error: $error";
            exit;
        }

        if (!isset($_GET['code'])) {
            echo "code is not set. wrong path?" . "\n";
            exit;
        }

        $code           = $_GET['code'];
        $access_token   = OAuthController::googleGetAccessToken($code);
        $user_info      = OAuthController::googleGetUserInfo($access_token);

        session_regenerate_id();

        $_SESSION['google_loggedin']    = true;
        $_SESSION['google_email']       = $user_info['email'];
        $_SESSION['google_name']        = isset($user_info['picture']) ? $user_info['given_name'] : '';
        $_SESSION['google_picture']     = isset($user_info['picture']) ? $user_info['picture'] : '';

        header('Location: /profile');
    }


    private static function googleGetAccessToken(string $code): string
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

        return $response['access_token'];
    }

    private static function googleGetUserInfo(string $access_token): mixed
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
}
