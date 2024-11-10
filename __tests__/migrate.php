<?php

require "src/migrate.php";

function DeleteMigrationFiles(string $name)
{
    $path = MIGRATIONS_DIR . date("Y-m-d") . "_" . $name;

    unlink($path . ".up.sql");
    unlink($path . ".down.sql");
}


function CheckIfTableExists(PDO $pdo, string $table_name): bool
{
    $query = "SELECT to_regclass('\"$table_name\"');";

    $res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    if ($res[0]["to_regclass"] !== null) {
        return true;
    }

    return false;
}

function AssertTableExists(PDO $pdo, string $table_name, string $error_msg)
{
    if (!CheckIfTableExists($pdo, $table_name)) {
        echo RED . $error_msg . "\n" . RESET;
        exit;
    }
}

function AssertTableNotExists(PDO $pdo, string $table_name, string $error_msg)
{
    if (CheckIfTableExists($pdo, $table_name)) {
        echo RED . $error_msg . "\n" . RESET;
        exit;
    }
}



function SingeTable()
{
    $pdo    = Connect();
    $name   = "tmp";


    GenereteMigrationFiles($name);

    AssertTableNotExists($pdo, $name, "TABLE $name ALREADY EXISTS");

    Update();

    AssertTableExists($pdo, $name, "TABLE $name WAS NOT CREATED");

    Rollback();

    AssertTableNotExists($pdo, $name, "TABLE $name WAS NOT DROPED");


    DeleteMigrationFiles($name);

    echo YELLOW . "Test " . __FUNCTION__ . " did not crash" . "\n" . RESET;
}

function TwoTable()
{
    $pdo        = Connect();
    $name1      = "tmp 1";
    $name2      = "tmp 2";


    GenereteMigrationFiles($name1);

    AssertTableNotExists($pdo, $name1, "TABLE $name1 ALREADY EXISTS");
    AssertTableNotExists($pdo, $name2, "TABLE $name2 ALREADY EXISTS");

    Update();

    AssertTableExists($pdo, $name1, "TABLE $name1 WAS NOT CREATED");
    AssertTableNotExists($pdo, $name2, "TABLE $name2 WAS CREATED");

    GenereteMigrationFiles($name2);

    Update();

    AssertTableExists($pdo, $name1, "TABLE $name1 WAS DROPPED ?");
    AssertTableExists($pdo, $name2, "TABLE $name2 WAS NOT CREATED");

    Rollback();

    AssertTableExists($pdo, $name1, "TABLE $name1 WAS DROPED");
    AssertTableNotExists($pdo, $name2, "TABLE $name2 WAS NOT DROPED");

    Rollback();

    AssertTableNotExists($pdo, $name1, "TABLE $name1 WAS NOT DROPED");
    AssertTableNotExists($pdo, $name2, "TABLE $name2 WAS CREATED ?");

    Update();

    AssertTableExists($pdo, $name1, "TABLE $name1 WAS NOT CREATED");
    AssertTableExists($pdo, $name2, "TABLE $name2 WAS NOT CREATED");

    Rollback();

    AssertTableNotExists($pdo, $name1, "TABLE $name1 WAS NOT DROPED");
    AssertTableNotExists($pdo, $name2, "TABLE $name2 WAS NOT DROPED");


    DeleteMigrationFiles($name1);
    DeleteMigrationFiles($name2);

    echo YELLOW . "Test " . __FUNCTION__ . " did not crash" . "\n" . RESET;
}

SingeTable();
TwoTable();
