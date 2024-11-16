<?php

use App\Routing\Router;
use App\Controllers\TaskController;

Router::getProtected("/ajax/task/fetchall", function () {
    TaskController::fetchAll();
});

Router::postProtected("/ajax/task/create", function () {
    TaskController::createTask();
});

Router::postProtected("/ajax/task/delete", function () {
    TaskController::deleteTaskById();
});

Router::postProtected("/ajax/task/update", function () {
    TaskController::updateTaskById();
});
