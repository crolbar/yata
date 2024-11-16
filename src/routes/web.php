<?php

use App\Routing\Router;
use App\Controllers\TaskController;

Router::getProtected("/", function () {
    TaskController::index();
});
