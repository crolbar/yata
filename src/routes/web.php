<?php

use App\Routing\Router;
use App\Controllers\TaskController;

Router::getProtected("/", function () {
    TaskController::index();
});

Router::getProtected("/d", function () {
    unset($_SESSION['tasks']);
});

Router::getProtected("/new", function () {
    Router::view("new");
});

Router::getProtected("/test", function () {
    Router::view("test");
});
