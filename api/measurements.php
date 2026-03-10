<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

require_once '../config/database.php';

$method   = $_SERVER['REQUEST_METHOD'];
$input    = json_decode(file_get_contents('php://input'), true);
$action   = $_GET['action'] ?? '';
$child_id = $_GET['child_id'] ?? null;

if ($method === 'GET' && $action === 'list' && $child_id) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM measurements WHERE child_id = ? ORDER BY date ASC");
    $stmt->bind_param("i", $child_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $measurements = [];
    while ($row = $result->fetch_assoc()) { $measurements[] = $row; }
    echo json_encode(["success"=>true,"measurements"=>$measurements]);
    $stmt->close(); $conn->close();
} elseif ($method === 'POST' && $action === 'add') {
    $cid    = $input['child_id'] ?? null;
    $height = $input['height'] ?? null;
    $weight = $input['weight'] ?? null;
    $date   = $input['date'] ?? date('Y-m-d');
    if (!$cid || !$height || !$weight) { echo json_encode(["success"=>false,"error"=>"Fields required."]); exit(); }
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO measurements (child_id, height, weight, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idds", $cid, $height, $weight, $date);
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $fetch  = $conn->prepare("SELECT * FROM measurements WHERE id = ?");
        $fetch->bind_param("i", $new_id);
        $fetch->execute();
        $row = $fetch->get_result()->fetch_assoc();
        echo json_encode(["success"=>true,"measurement"=>$row]);
        $fetch->close();
    } else {
        echo json_encode(["success"=>false,"error"=>"Failed to save."]);
    }
    $stmt->close(); $conn->close();
} else {
    echo json_encode(["success"=>false,"error"=>"Invalid request"]);
}
?>
