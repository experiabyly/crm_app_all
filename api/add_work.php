<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["content"]) && !empty($data["createdate"]) && !empty($data["duedate"]) && isset($data["status"])&& !empty($data["id_user"])) {
    $status = intval($data["status"]);
    $id_user = intval($data["id_user"]);
    $createdate = trim($data["createdate"]);
    $duedate = trim($data["duedate"]);
    $content = trim($data["content"]);
    $sql = "INSERT INTO work (content, createdate, duedate, status, id_user) VALUES (?, ?, ?, ?, ?)";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $content, $createdate, $duedate, $status, $id_user);  
    $stmt->execute(); 
    $lastid = $conn->insert_id;
    $stmtres = $conn->prepare("SELECT * FROM work WHERE id_work = ?");
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
