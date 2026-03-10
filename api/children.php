<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

require_once '../config/database.php';

$method  = $_SERVER['REQUEST_METHOD'];
$input   = json_decode(file_get_contents('php://input'), true);
$action  = $_GET['action'] ?? '';
$user_id = $_GET['user_id'] ?? null;

if ($method === 'GET' && $action === 'list' && $user_id) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT c.*,
               m.height AS last_height,
               m.weight AS last_weight,
               m.date   AS last_date,
               m.bmi    AS last_bmi
        FROM children c
        LEFT JOIN measurements m ON m.id = (
            SELECT id FROM measurements
            WHERE child_id = c.id
            ORDER BY date DESC LIMIT 1
        )
        WHERE c.user_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $children = [];
    while ($row = $result->fetch_assoc()) { $children[] = $row; }
    echo json_encode(["success"=>true,"children"=>$children]);
    $stmt->close(); $conn->close();
} elseif ($method === 'POST' && $action === 'add') {
    $uid    = $input['user_id'] ?? null;
    $name   = trim($input['name'] ?? '');
    $dob    = $input['dob'] ?? '';
    $gender = $input['gender'] ?? '';
    if (!$uid || !$name || !$dob || !$gender) { echo json_encode(["success"=>false,"error"=>"All fields required."]); exit(); }
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO children (user_id, name, dob, gender) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $uid, $name, $dob, $gender);
    if ($stmt->execute()) {
        echo json_encode(["success"=>true,"child_id"=>$conn->insert_id]);
    } else {
        echo json_encode(["success"=>false,"error"=>"Failed to add child."]);
    }
    $stmt->close(); $conn->close();
} else {
    echo json_encode(["success"=>false,"error"=>"Invalid request"]);
}
?>
