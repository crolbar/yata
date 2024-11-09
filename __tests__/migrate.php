<?php

# TODO

function DeleteMigrationFiles(string $name)
{
    $path = MIGRATIONS_DIR . date("Y-m-d") . "_" . $name;

    unlink($path . ".up.sql");
    unlink($path . ".down.sql");
}

require "src/migrate.php";

$name = "tmp";

GenereteMigrationFiles($name);
Update();
Rollback();
DeleteMigrationFiles($name);
