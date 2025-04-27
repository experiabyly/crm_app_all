<?php
header('Content-Type: application/json');
include "db.php";
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Lỗi kết nối cơ sở dữ liệu"]));
}
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

if (!empty($data["id_account"])) {
    $id_account = $conn->real_escape_string($data["id_account"]);
    $res = [];
    
    // Kiểm tra tài khoản trong database
    $stmt = $conn->prepare("SELECT * FROM notification WHERE id_account = ?");
    $stmt->bind_param("s", $id_account);
    $stmt->execute();
    $resuser = $stmt->get_result();
    if ($resuser->num_rows > 0) {
        while ($row = $resuser->fetch_assoc()) {
            $res[] = $row;
        }
    }
    $stmt->close();
    echo json_encode(["status" => "success", "data" => $res, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
