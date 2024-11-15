<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Util\Env;
use App\Lib\GoogleJWT;

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
        setcookie("jwt", "", time() - 3600, "/", "", true, true);
        header('Location: /login');
    }

    public static function profile(): void
    {
        self::checkLogedIn();
        Router::view('profile');
    }

    public static function checkLogedIn(): void
    {
        ob_start();
        if (!isset($_COOKIE["jwt"])) {
            self::logout();
            exit;
        }

        if ($_COOKIE["jwt"] === "") {
            self::logout();
            exit;
        }

        $jwt = GoogleJWT::verifyJwtSignature($_COOKIE["jwt"]);
        if ($jwt === false) {
            self::logout();
            exit;
        }

        ob_end_flush();
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
        $tokens         = GoogleJWT::getTokens($code);

        $access_token   = $tokens['access_token'];
        $user_info      = GoogleJWT::getUserInfo($access_token);


        setcookie(
            "jwt",
            $tokens["id_token"],
            [
                "httponly" => true,
                "secure" => true,
                "path" => "/",
                "samesite" => "Strict"
            ]
        );

        session_regenerate_id();

        $_SESSION['google_loggedin']    = true;
        $_SESSION['google_email']       = $user_info['email'];
        $_SESSION['google_name']        = isset($user_info['given_name']) ? $user_info['given_name'] : '';
        $_SESSION['google_picture']     = isset($user_info['picture']) ? $user_info['picture'] : '';

        Router::view("redirecting", ["url" => "/profile"]);
    }
}
