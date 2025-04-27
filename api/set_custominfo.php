<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_business"]) && !empty($data["name"]) && !empty($data["type"]) && !empty($data["description"])) {
    
    $id_business = intval($data["id_business"]);
    $name = trim($data["name"]);
    $type = trim($data["type"]);
    $description = trim($data["description"]);
    $sql = "INSERT INTO custominfo (id_business, name, type, description) VALUES (?, ?, ?, ?)";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $id_business, $name, $type, $description);  
    $stmt->execute(); 
    $lastid = $conn->insert_id;
    $stmtres = $conn->prepare("SELECT * FROM custominfo WHERE id_custominfo = ?");
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
