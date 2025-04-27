<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["name"]) && !empty($data["address"]) && !empty($data["id_account"]) && !empty($data["email"]) && !empty($data["phone"])) {
    
    $id_account = intval($data["id_account"]);
    $name = trim($data["name"]);
    $address = trim($data["address"]);
    $email = trim($data["email"]);
    $phone = trim($data["phone"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit();
   }

            // Kiểm tra số điện thoại (ít nhất 10 chữ số)
    if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
        echo json_encode(["status" => "error", "message" => "Invalid phone number"]);
        exit();
   }
        
    $checkStmt = $conn->prepare("SELECT * FROM users WHERE id_account = ?");
    $checkStmt->bind_param("i", $id_account);
    $checkStmt->execute();
    $checkStmt->store_result();
    $sql = "";   
    if ($checkStmt->num_rows > 0) {
        $sql = "UPDATE users SET name = ?, address = ? WHERE id_account = ?";
    } else {
        // Kiểm tra định dạng email
        $sql = "INSERT INTO users (name, address, id_account) VALUES (?, ?, ?)";   
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $address, $id_account);        
    $stmt->execute();
    $lastid = $id_account;
    $stmtres = $conn->prepare("SELECT name, address FROM users WHERE id_account = '$lastid'");
    $stmtres->execute();
    $resuser = $stmtres->get_result();
    $rowuser = $resuser->fetch_assoc();
    $stmtres->close();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE account SET email = ?, phone = ? WHERE id_account = ?");
    $stmt->bind_param("ssi", $email, $phone, $id_account);  
    $stmt->execute();
    $stmtres = $conn->prepare("SELECT email, phone FROM account WHERE id_account = '$lastid'");
    $stmtres->execute();
    $resaccount = $stmtres->get_result();
    $rowaccount = $resaccount->fetch_assoc();
    $stmtres->close();
    $stmt->close();        
    $userinfo = array_merge($rowuser, $rowaccount);
    echo json_encode(["status" => "success", "data" => $userinfo, "message" => "Cập nhật thông tin thành công"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
