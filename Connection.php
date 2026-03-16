<?php
$server = '127.0.0.1';
$uname = 'root';
$password = '';
$db = 'user';
$port = 3307;

try {
    $conn = mysqli_connect($server, $uname, $password, $db, $port);
} catch (mysqli_sql_exception $e) {
    die('Database Connection Failed: ' . $e->getMessage());
}

