<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_product"]) && !empty($data["id_measurement"]) && !empty($data["name"]) && !empty($data["description"]) && !empty($data["price"]) && !empty($data["amount"])) {
    $id_product = intval($data["id_product"]);
    $id_measurement = intval($data["id_measurement"]);
    $name = trim($data["name"]);
    $description = trim($data["description"]);
    $price = trim($data["price"]);
    $amount = trim($data["amount"]);
    $sql = "UPDATE product SET id_measurement = ?, name = ?, description = ?, price = ?, amount = ? where id_product = ?";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssi", $id_measurement, $name, $description, $price, $amount, $id_product);  
    $stmt->execute(); 
    $stmtres = $conn->prepare("SELECT * FROM product WHERE id_product = ?");
    $stmtres->bind_param("i", $id_product);
    $stmtres->execute();
    $resuser = $stmtres->get_result();

    $rowuser = $resuser->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $rowuser, "message" => "Cập nhật thành công"]);
    $stmtres->close();
    $stmt->close();     
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
