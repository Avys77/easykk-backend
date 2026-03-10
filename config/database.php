<?php
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$name = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
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
