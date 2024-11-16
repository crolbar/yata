<?php

use App\Routing\Router;

Router::get("/global.css", function () {
    Router::style("global");
});

Router::get("/tailwind.css", function () {
    Router::style("tailwind");
});
