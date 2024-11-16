<?php

use App\Routing\Router;

Router::get("404", function () {
    Router::view("not_found");
});
