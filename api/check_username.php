<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["username"]) ) {
    $username = trim($data["username"]);
   $checkStmt = $conn->prepare("SELECT * FROM account WHERE username = ?");
   $checkStmt->bind_param("s", $username);
   $checkStmt->execute();
   $checkStmt->store_result();
   if ($checkStmt->num_rows > 0) {
       echo json_encode(["status" => "failed", "data" => false, "message" => "Tên tài khoản đã tồn tại"]);
       exit();
   } else {
        echo json_encode(["status" => "success", "data" => true, "message" => "Tên tài khoản hợp lệ"]);
        exit();        
    }   
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
