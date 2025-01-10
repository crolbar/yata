<?php

use App\Controllers\OAuthController;
use App\Routing\Router;

Router::get("/login", function () {
    OAuthController::login();
});

Router::get("/logout", function () {
    OAuthController::logout();
});

Router::get("/login/google-oauth", function () {
    OAuthController::googleOAth();
});

Router::get("/redirect/google-oauth", function () {
    OAuthController::googleRedirect();
});
