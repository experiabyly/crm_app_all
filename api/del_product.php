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

if (!empty($data["id_product"])) {
    $id_product = $conn->real_escape_string($data["id_product"]);
    // Kiểm tra tài khoản trong database
    $stmt = $conn->prepare("DELETE FROM product WHERE id_product = ?");
    $stmt->bind_param("s", $id_product);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["status" => "success", "message" => "Delete information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
