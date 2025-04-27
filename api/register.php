<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["username"]) && !empty($data["password"]) && !empty($data["id_role"]) && !empty($data["id_business"]) && !empty($data["email"]) && !empty($data["phone"])) {
    
    $id_role = intval($data["id_role"]);
    $id_business = intval($data["id_business"]);
    $username = trim($data["username"]);
    $password = trim($data["password"]);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
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
        
   $checkStmt = $conn->prepare("SELECT * FROM account WHERE username = ?");
   $checkStmt->bind_param("s", $username);
   $checkStmt->execute();
   $checkStmt->store_result();
   if ($checkStmt->num_rows > 0) {
       echo json_encode(["status" => "failed", "message" => "Tên tài khoản đã tồn tại"]);
       exit();
   }
    else {
        // Kiểm tra định dạng email
        $sql = "INSERT INTO account (username, password, id_role, id_business, email, phone) VALUES (?, ?, ?, ?, ?, ?)";   
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiss", $username, $hashed_password, $id_role, $id_business, $email, $phone);  
        $stmt->execute(); 
        $lastid = $conn->insert_id;
        $stmtres = $conn->prepare("SELECT * FROM account WHERE id_account = ?");
        $stmtres->bind_param("i", $lastid);
        $stmtres->execute();
        $resuser = $stmtres->get_result();

        $sqlu = "INSERT INTO users (name, address, id_account, access_token) VALUES (?, ?, ?, ?)";
        $name = "";
        $address = "";
        $access_token = "";
        $stmtu = $conn->prepare($sqlu);
        $stmtu->bind_param("ssis", $name, $address, $lastid, $access_token);
        $stmtu->execute(); 

        $rowuser = $resuser->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $rowuser, "message" => "Đăng ký thành công"]);
        $stmtres->close();
        $stmt->close();        
    }   
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
