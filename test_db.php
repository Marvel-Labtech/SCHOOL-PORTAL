<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "STEP 1<br>";

session_start();

echo "STEP 2<br>";

include('db.php');

echo "STEP 3<br>";

var_dump($conn);

echo "<br>STEP 4";

?>