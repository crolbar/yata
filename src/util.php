<?php

namespace App\Util;

define("ENV_FILE", __DIR__ . "/../.env");

class Env {
    public static function ParseEnv(): void
    {
        if (file_exists(ENV_FILE)) {
            $lines = file(ENV_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) {
                    continue;
                }
                list($name, $value) = explode('=', $line, 2);
                putenv(trim($name) . "=" . trim($value));
            }
        }
    }
}
