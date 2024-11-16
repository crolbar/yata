<?php

namespace App\Controllers;

use App\Routing\Router;
use App\Util\Env;
use App\Lib\GoogleJWT;
use App\Models\UserModel;

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

    public static function createSession(string $jwt, string $id, string $email, string $name, string $picture): void
    {
        session_start();
        session_regenerate_id();

        setcookie(
            "jwt",
            $jwt,
            [
                "httponly" => true,
                "secure" => true,
                "path" => "/",
                "samesite" => "Strict"
            ]
        );

        $_SESSION['id']         = $id;
        $_SESSION['email']      = $email;
        $_SESSION['name']       = $name;
        $_SESSION['picture']    = $picture;
    }

    private static function refreshJWT(string $sub): void
    {
        $refresh_token = UserModel::getRefreshToken($sub);

        if ($refresh_token === false) {
            self::logout();
            exit;
        }

        $jwt = GoogleJWT::getJWTFromRefresh($refresh_token);
        setcookie(
            "jwt",
            $jwt,
            [
                "httponly" => true,
                "secure" => true,
                "path" => "/",
                "samesite" => "Strict"
            ]
        );
    }

    // we can use this as an session_start()-er
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

        // maybe uneeded ?
        $jwt = json_decode($jwt, true);
        if (UserModel::isNewUser($jwt["sub"]) === true) {
            self::logout();
            exit;
        }

        // jwt expired or expires in less than 5 min
        if ($jwt["exp"] - time() < 300) {
            self::refreshJWT($jwt["sub"]);
        }

        session_start();
        if (!isset($_SESSION["id"])) {
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
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ];

        header('Location: ' . $google_oauth_uri . '?' . http_build_query($params));
    }

    public static function googleRedirect(): void
    {
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
        $refresh_token  = $tokens["refresh_token"];
        $user_info      = GoogleJWT::getUserInfo($access_token);


        $sub        = $user_info['sub'];
        $email      = $user_info['email'];
        $name       = isset($user_info['given_name']) ? $user_info['name'] : '';
        $picture    = isset($user_info['picture']) ? $user_info['picture'] : '';
        $id         = UserModel::isNewUser($sub);

        if ($id === true) {
            UserModel::createUser($name, $email, $sub, $refresh_token);
            $id = UserModel::isNewUser($sub);
        }

        self::createSession($tokens["id_token"], $id, $email, $name, $picture);

        Router::view("redirecting", ["url" => "/"]);
    }
}
