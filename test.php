<?php
const RED    = "\033[0;31m";
const GREEN  = "\033[0;32m";
const YELLOW = "\033[0;33m";
const RESET  = "\033[0m";

$sep = "=============================================================\n";


$tests = glob(__DIR__ . "/__tests__/*.php");

foreach ($tests as $test) {
    echo YELLOW . "\nRunning test $test\n" . RESET;
    echo $sep;

    require_once $test;

    echo $sep;
    echo GREEN . "Test did not crash\n" . RESET;
}
