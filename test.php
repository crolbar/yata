<?php

$green  = "\033[0;32m";
$yellow = "\033[0;33m";
$reset  = "\033[0m";


$sep = "=============================================================\n";


$tests = glob(__DIR__ . "/__tests__/*.php");

foreach ($tests as $test) {
    echo $yellow . "\nRunning test $test\n" . $reset;
    echo $sep;

    require_once $test;

    echo $sep;
    echo $green . "Test did not crash\033[0m\n" . $reset;
}
