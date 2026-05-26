<?php
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "coreops";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    date_default_timezone_set('America/New_York');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    