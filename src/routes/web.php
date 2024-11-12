<?php


use App\Routing\Router;
use App\Controllers\TaskController;

Router::get("/", function () {
    TaskController::index();
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


Router::get("/global.css", function () {
    Router::style("global");
});

Router::get("/tailwind.css", function () {
    Router::style("tailwind");
});

Router::get("404", function () {
    Router::view("not_found");
});
