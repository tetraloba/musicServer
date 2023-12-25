<?php
$dsn = "dbname=music_server;host=localhost;charset=utf8mb4";
$username = "root";
$password = "";
$driver_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]

function db_connect(){
    return new PDO($dsn, $username, $password, $driver_options);
}

?>