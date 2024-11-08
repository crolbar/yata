<?php

$routes = [
    "/" => "view/home.php",
    "/global.css" => "view/css/global.css"
];

$page_not_found_page = "view/not_found.php";


$req_uri = $_SERVER["REQUEST_URI"];
$req_uri = strtok($req_uri, '?');

if (str_ends_with($req_uri, ".css")) {
    header('Content-Type: text/css');
}

if (array_key_exists($req_uri, $routes)) {
    require $routes[$req_uri];
} else {
    require $page_not_found_page;
}
