<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_business"]) && !empty($data["id_measurement"]) && !empty($data["name"]) && !empty($data["description"]) && !empty($data["price"]) && !empty($data["amount"])) {
    $id_measurement = intval($data["id_measurement"]);
    $id_business = intval($data["id_business"]);
    $name = trim($data["name"]);
    $description = trim($data["description"]);
    $price = trim($data["price"]);
    $amount = trim($data["amount"]);
    $sql = "INSERT INTO product (id_business, id_measurement, name, description, price, amount) VALUES (?, ?, ?, ?, ?, ?)";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $id_business, $id_measurement, $name, $description, $price, $amount);  
    $stmt->execute(); 
    $lastid = $conn->insert_id;
    $stmtres = $conn->prepare("SELECT * FROM product WHERE id_product = ?");
    $stmtres->bind_param("i", $lastid);
    $stmtres->execute();
    $resuser = $stmtres->get_result();

    $rowuser = $resuser->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $rowuser, "message" => "Thêm thành công"]);
    $stmtres->close();
    $stmt->close();        
    
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
