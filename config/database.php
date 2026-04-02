<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$name = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: '3306';

function getConnection() {
    global $host, $user, $pass, $name, $port;
    $conn = new mysqli($host, $user, $pass, $name, (int)$port);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["error" => "DB connection failed: " . $conn->connect_error]);
        exit();
    }
    return $conn;
}
?>
