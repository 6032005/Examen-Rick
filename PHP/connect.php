<?php

$servername = "localhost:3306";
$databasename = "Rijschool";
$username = "root";
$password = "";


try {
    $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit();
}