<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");
require_once '../config/database.php';

$input  = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $email   = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $name    = trim($input['name'] ?? '');
    $phone   = trim($input['phone'] ?? '');
    $address = trim($input['address'] ?? '');
    if (!$email || !$password) { echo json_encode(["success"=>false,"error"=>"Email and password required."]); exit(); }
    $conn   = getConnection();
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt   = $conn->prepare("INSERT INTO users (email, password, name, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $hashed, $name, $phone, $address);
    if ($stmt->execute()) {
        echo json_encode(["success"=>true,"user"=>["id"=>$conn->insert_id,"email"=>$email,"name"=>$name,"phone"=>$phone,"address"=>$address]]);
    } else {
        echo json_encode(["success"=>false,"error"=>($conn->errno==1062)?"Email already registered.":"Registration failed."]);
    }
    $stmt->close(); $conn->close();

} elseif ($action === 'login') {
    $email    = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    if (!$email || !$password) { echo json_encode(["success"=>false,"error"=>"Fields required."]); exit(); }
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, email, password, name, phone, address FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(["success"=>true,"user"=>["id"=>$user['id'],"email"=>$user['email'],"name"=>$user['name'],"phone"=>$user['phone'],"address"=>$user['address']]]);
    } else {
        echo json_encode(["success"=>false,"error"=>"Invalid email or password."]);
    }
    $stmt->close(); $conn->close();

} else {
    echo json_encode(["success"=>false,"error"=>"Invalid request"]);
}
?>
