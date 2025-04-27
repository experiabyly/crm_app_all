<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_account"]) && !empty($data["content"]) && !empty($data["timecreated"]) && isset($data["readstatus"])) {

    $id_account = intval($data["id_account"]);
    $content = trim($data["content"]);
    $timecreated = trim($data["timecreated"]);
    $readstatus = intval($data["readstatus"]);
    $checkStmt = $conn->prepare("INSERT INTO notification(id_account, content, timecreated, readstatus) VALUES(?, ?, ?, ?)");
    $checkStmt->bind_param("issi", $id_account, $content, $timecreated, $readstatus);
    $checkStmt->execute();
    $lastid = $conn->insert_id;
    $stmt = $conn->prepare("SELECT * FROM notification WHERE id_noti = ?");
    $stmt->bind_param("i", $lastid);
    $stmt->execute();
    $resuser = $stmt->get_result();
    $rowuser = $resuser->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $rowuser, "message" => "Thêm thành công"]);
    $stmt->close();        
    
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
