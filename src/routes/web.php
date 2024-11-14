<?php


use App\Controllers\OAuthController;
use App\Routing\Router;
use App\Controllers\TaskController;

Router::get("/", function () {
    header('Location: profile');
    //TaskController::index();
});


Router::get("/login", function () {
    OAuthController::login();
});

Router::get("/logout", function () {
    OAuthController::logout();
});

Router::get("/profile", function () {
    OAuthController::profile();
});


Router::get("/login/google-oauth", function () {
    OAuthController::googleOAth();
});

Router::get("/redirect/google-oauth", function () {
    OAuthController::googleRedirect();
});


Router::get("/ajax/task/fetchall", function () {
    TaskController::fetchAll();
});

Router::post("/ajax/task/create", function () {
    TaskController::createTask();
});

Router::post("/ajax/task/delete", function() {
    TaskController::deleteTaskById();
});

Router::post("/ajax/task/update", function() {
    TaskController::updateTaskById();
});



Router::get("/global.css", function () {
    Router::style("global");
});

Router::get("/tailwind.css", function () {
    Router::style("tailwind");
});

Router::get("404", function () {
    Router::view("not_found");
});
