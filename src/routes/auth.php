<?php

use App\Controllers\OAuthController;
use App\Routing\Router;
use App\Controllers\NotifyController;

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

Router::postProtected("/api/set-fcm-token", function () {
    NotifyController::update_fcm_device_token();
});
