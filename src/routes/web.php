<?php


use App\Routing\Router;
use App\Controllers\TaskController;

Router::get("/", function () {
    TaskController::index();
});

Router::post("/", function () {
    if (isset($_POST["id"])) {
        TaskController::deleteTaskById($_POST["id"]);
    } else if (isset($_POST["title"])) {
        TaskController::createTask($_POST["title"]);
    }
});


Router::get("/global.css", function () {
    Router::style("global");
});

Router::get("404", function () {
    Router::view("not_found");
});
