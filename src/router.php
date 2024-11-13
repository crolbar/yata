<?php

namespace App\Routing;

class Router
{
    private $routes = [];
    private static $instance = null;

    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    /*
    * @param callable $callback
    */
    public function addRoute(string $path, string $method, callable $callback): void
    {
        $this->routes[] = [
            "method"    => $method,
            "path"      => $path,
            "callback"  => $callback,
        ];
    }


    /*
    * @param callable $callback
    */
    public static function get(string $path, callable $callback): void
    {
        self::getInstance()->addRoute($path, "GET", $callback);
    }

    /*
    * @param callable $callback
    */
    public static function post(string $path, callable $callback): void
    {
        self::getInstance()->addRoute($path, "POST", $callback);
    }

    public function handle(string $path, string $method): void
    {
        $not_found = null;

        foreach ($this->routes as $route) {
            $p = $route["path"];
            if ($route['method'] != $method) {
                continue;
            }

            if ($route['path'] == "404") {
                $not_found = $route["callback"];
                continue;
            }

            if ($route['path'] != $path) {
                continue;
            }

            call_user_func($route["callback"]);
            return;
        }


        if ($not_found != null) {
            call_user_func($not_found);
            return;
        }

        echo "404";
    }

    /*
    * @param array $args
    */
    public static function view(string $view, array $args = []): void
    {
        extract($args);
        require "views/" . $view . ".php";
    }

    public static function style(string $style): void
    {
        header('Content-Type: text/css');
        require "views/css/" . $style . ".css";
    }
}

function importControllers()
{
    $controllers = glob(__DIR__ . "/controllers/*");
    foreach ($controllers as $controller) {
        require $controller;
    }
}

function importModels()
{
    $models = glob(__DIR__ . "/models/*");
    foreach ($models as $model) {
        require $model;
    }
}

function importConfig()
{
    $configs = glob(__DIR__ . "/config/*");
    foreach ($configs as $config) {
        require $config;
    }
}

function importRoutes()
{
    $routes = glob(__DIR__ . "/routes/*");
    foreach ($routes as $route) {
        require $route;
    }
}

function importUtil()
{
    require "util.php";
}

function handleRequest()
{
    $req_path   = $_SERVER["REQUEST_URI"];
    $req_path   = strtok($req_path, '?');
    $req_method = $_SERVER["REQUEST_METHOD"];

    Router::getInstance()->handle($req_path, $req_method);
}

// imports
importControllers();
importModels();
importConfig();
importRoutes();
importUtil();

handleRequest();
