<?php

use App\Routing\Router;
use App\Controllers\TaskController;

Router::getProtected("/", function () {
    TaskController::index();
});

Router::getProtected("/new", function () {
    Router::view("new");
});

Router::getProtected("/test", function () {
    Router::view("test");
});
