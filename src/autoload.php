<?php

function autoload()
{
    spl_autoload_register(function ($class) {
        $class = str_replace('\\', '/', $class);

        $map = [
            "App/Routing/Router"    => __DIR__ . "/router.php",
            "App/Util/Env"          => __DIR__ . "/util.php",
            "App/Controllers/*"     => __DIR__ . "/controllers",
            "App/Models/*"          => __DIR__ . "/models",
            "App/Lib/*"             => __DIR__ . "/lib",
            "App/Config/*"          => __DIR__ . "/config",
        ];

        foreach ($map as $pattern => $file) {
            if (fnmatch($pattern, $class)) {
                if (is_dir($file)) {
                    $class = explode("/", $class)[2];
                    $file = "$file/$class.php";
                }

                if (file_exists($file)) {
                    require $file;
                }
            }
        }
    });
}

autoload();
