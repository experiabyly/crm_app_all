<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_customer"]) && !empty($data["id_user"]) && !empty($data["date"])) {
    $id_customer = intval($data["id_customer"]);
    $id_user = intval($data["id_user"]);
    $date = trim($data["date"]);
    $sql = "INSERT INTO orders (id_customer, id_user, date) VALUES (?, ?, ?)";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_customer, $id_user, $date);  
    $stmt->execute(); 
    $lastid = $conn->insert_id;
    $stmtres = $conn->prepare("SELECT * FROM orders WHERE id_order = ?");
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
