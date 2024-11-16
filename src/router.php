<?php

namespace App\Routing;

use App\Controllers\OAuthController;

class Router
{
    private $routes = [];
    private static $instance = null;

    private static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    private static function loadRoutes(): void
    {
        $routes = glob(__DIR__ . "/routes/*");
        foreach ($routes as $route) {
            require $route;
        }
    }

    /*
    * @param callable $callback
    */
    public function addRoute(
        string $method,
        string $path,
        callable $callback,
        bool $is_protected
    ): void {
        $this->routes[] = [
            "method"        => $method,
            "path"          => $path,
            "callback"      => $callback,
            "is_protected"  => $is_protected,
        ];
    }


    /*
    * @param callable $callback
    */
    public static function get(string $path, callable $callback): void
    {
        self::getInstance()->addRoute("GET", $path, $callback, false);
    }

    /*
    * @param callable $callback
    */
    public static function post(string $path, callable $callback): void
    {
        self::getInstance()->addRoute("POST", $path, $callback, false);
    }

    /*
    * @param callable $callback
    */
    public static function getProtected(string $path, callable $callback): void
    {
        self::getInstance()->addRoute("GET", $path, $callback, true);
    }

    /*
    * @param callable $callback
    */
    public static function postProtected(string $path, callable $callback): void
    {
        self::getInstance()->addRoute("POST", $path, $callback, true);
    }

    public static function handleRequest(): void
    {
        self::loadRoutes();

        $req_path   = $_SERVER["REQUEST_URI"];
        $req_path   = strtok($req_path, '?');
        $req_method = $_SERVER["REQUEST_METHOD"];

        self::getInstance()->handle($req_path, $req_method);
    }

    private static function handleProtected(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            OAuthController::checkLogedIn();
        }
    }

    private function handle(string $path, string $method): void
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

            if ($route['is_protected']) {
                self::handleProtected();
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
