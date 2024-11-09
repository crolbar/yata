<?php

define("MIGRATIONS_DIR", __DIR__ . "/migrations/"); // make sure this ends with '/' !! (relative to the migrate.php file)
define("ENV_FILE", __DIR__ . "/../.env");

function ParseEnv()
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

function Connect(): PDO
{
    ParseEnv();
    $host       = getenv("HOST");
    $password   = getenv("PASSWORD");
    $user       = getenv("USER");
    $db         = getenv("DB");
    $uri        = "pgsql:host=$host;dbname=$db;";

    try {
        $pdo = new PDO($uri, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        if ($pdo) {
            echo "Connected to pgsql:$host/$db" . "\n";
        }
    } catch (PDOException $e) {
        die("DB connection failed: " . $e->getMessage());
    }

    return $pdo;
}


function CheckIfMigrationsTableExists(PDO $pdo): bool
{
    $table_name = "migrations";
    $query = "SELECT to_regclass('$table_name');";

    $res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    if ($res[0]["to_regclass"] !== null) {
        return true;
    }

    return false;
}

function CreateMigrationsTable(PDO $pdo)
{
    $query = "CREATE TABLE migrations (id SERIAL PRIMARY KEY, migration_name varchar(255) NOT NULL, batch int NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    $ra = $pdo->exec($query);

    if ($ra !== null) {
        echo "Migrations table created.\n";
    }
}

function GetLastBatchNumber(PDO $pdo): int
{
    $query = "SELECT MAX(batch) FROM migrations";

    $res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    if ($res[0]["max"] === null) {
        return -1;
    }

    return $res[0]["max"];
}

function GetAppliedMigrations(PDO $pdo): array
{
    $query = "SELECT migration_name FROM migrations";
    $res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    $applied_migrations = [];

    foreach ($res as $row) {
        array_push($applied_migrations, $row["migration_name"]);
    }

    return $applied_migrations;
}

function Update()
{
    $pdo = Connect();

    if (!CheckIfMigrationsTableExists($pdo)) {
        CreateMigrationsTable($pdo);
    }

    $current_batch_number = GetLastBatchNumber($pdo) + 1;
    echo "current_batch_number: $current_batch_number\n";

    $applied_migrations = GetAppliedMigrations($pdo);
    PrintAppliedMigrations($applied_migrations);

    $migrations = glob(MIGRATIONS_DIR . "*.up.sql");


    foreach ($migrations as $migration) {
        echo "\n" . $migration . "\n";

        $name = basename($migration, ".up.sql");

        if (in_array($name, $applied_migrations)) {
            echo "Skipping migration '$name'. Already applied." . "\n";
            continue;
        }

        $query = file_get_contents($migration);
        $pdo->exec($query);

        $query = "INSERT INTO migrations (migration_name, batch) VALUES ('$name', $current_batch_number)";
        $pdo->exec($query);

        echo "Migration '$name' applied\n";
    }
}

function GetBatchNames(PDO $pdo, $batch_number): array
{
    $query = "SELECT migration_name FROM migrations WHERE batch = $batch_number";
    $res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    $last_batch_names = [];

    foreach ($res as $row) {
        array_push($last_batch_names, $row["migration_name"]);
    }

    return $last_batch_names;
}

function Rollback()
{
    $pdo = Connect();

    if (!CheckIfMigrationsTableExists($pdo)) {
        echo "Migrations table does not exist. exiting..\n";
        exit;
    }

    $last_batch_number = GetLastBatchNumber($pdo);
    if ($last_batch_number == -1) {
        echo "No migrations to rollback.\n";
        exit;
    }

    $last_batch_names = GetBatchNames($pdo, $last_batch_number);

    foreach ($last_batch_names as $name) {
        $down_query_path = MIGRATIONS_DIR . "$name.down.sql";

        if (!file_exists($down_query_path)) {
            echo "Down migration query '$down_query_path' does not exist." . "\n";
            continue;
        }

        echo "\n" . $down_query_path . "\n";
        $query = file_get_contents($down_query_path);
        $pdo->exec($query);

        echo "Migration '$name' reverted\n";
    }

    $query = "DELETE FROM migrations WHERE batch = $last_batch_number";
    $pdo->exec($query);

    echo "\n" . "Migration batch '$last_batch_number' reverted.\n";
}

function GenereteMigrationFiles(string $name)
{
    $path = MIGRATIONS_DIR . date("Y-m-d") . "_" . $name;

    if (file_exists($path . ".up.sql") || file_exists($path . ".down.sql")) {
        echo "Migration with name `$name` already exists.. Exiting\n";
        return;
    }

    $f = fopen($path . ".up.sql", "w");
    fwrite($f, "-- GENERATED FROM migrate.php\n\nCREATE TABLE $name (\n    id SERIAL PRIMARY KEY,\n    name varchar(255) NOT NULL,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n)");
    $f = fopen($path . ".down.sql", "w");
    fwrite($f, "-- GENERATED FROM migrate.php\n\nDROP TABLE IF EXISTS $name;");
}

function Main(array $argv)
{
    if (sizeof($argv) < 2) {
        PrintHelp();
        exit;
    }

    if ($argv[1] == "up") {
        echo "Updating..\n\n";
        Update();
    } elseif ($argv[1] == "down") {
        echo "Rollbacking..\n\n";
        Rollback();
    } elseif ($argv[1] == "new") {
        GenereteMigrationFiles($argv[2]);
    } else {
        PrintHelp();
    }
}

function PrintAppliedMigrations(array $applied_migrations)
{
    echo "Applied migrations: ";
    foreach ($applied_migrations as $am) {
        echo "$am, ";
    }
    echo "\n";
}

function PrintHelp()
{

    echo "\n" .
        "Example: php migrate.php [up, down]"                       . "\n" .
        "Example: php migrate.php new users"                        . "\n" .
        "Options:"                                                  . "\n" .
        "up         Update schema"                                  . "\n" .
        "down       Rollback to prev batch"                         . "\n" .
        "new <NAME> Create up, down files in the MIGRATIONS_DIR"    . "\n";
}
