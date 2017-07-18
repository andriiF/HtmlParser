#!/usr/bin/php
<?php
error_reporting(0);
require_once('Parser.php');
while (true) {
    echo'>';
    $handle = fopen("php://stdin", "r");
    $string = strtolower(fgets($handle));
    $string = trim($string);
    $file = fopen("result.csv", "w");
    fclose($file);
    $replaced = preg_replace('/\s\s+/', ' ', $string);
    if ($string == "exit") {
        exit();
    }
    $phrase = explode(" ", $replaced);
    $parser = new Parser($phrase[0]);
    if (count($phrase) > 1) {
        $parser->getResult($phrase[1]);
    } else {
        $parser->getResult();
    }
    echo "\n";
}