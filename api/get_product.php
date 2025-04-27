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
    $stmt = $conn->prepare("SELECT id_measurement, name, description, price, amount FROM product WHERE id_product = ?");
    $stmt->bind_param("s", $id_product);
    $stmt->execute();
    $resuser = $stmt->get_result();
    $row = $resuser->fetch_assoc();
    $stmtmes = $conn->prepare("SELECT name, description FROM measurement WHERE id_measurement = ?");
    $stmtmes->bind_param("s", $row['id_measurement']);
    $stmtmes->execute();
    $resmes = $stmtmes->get_result();
    $row['measurement'] = $resmes->fetch_assoc();
    unset($row['id_measurement']);
    $product = $row; 
    $stmt->close();
    echo json_encode(["status" => "success", "data" => $product, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
