<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_customer"]) && !empty($data["name"]) && !empty($data["phone"]) && !empty($data["email"]) && !empty($data["birthday"]) && !empty($data["address"]) && isset($data["gender"]) && isset($data["id_type"]) && !empty($data["more"])) {
    $id_customer = intval($data["id_customer"]);
    $name = trim($data["name"]);
    $phone = trim($data["phone"]);
    $email = trim($data["email"]);
    $birthday = trim($data["birthday"]);
    $address = trim($data["address"]);
    $gender = trim($data["gender"]);
    $id_type = trim($data["id_type"]);
    $more = trim($data["more"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit();
    }

            // Kiểm tra số điện thoại (ít nhất 10 chữ số)
    if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
        echo json_encode(["status" => "error", "message" => "Invalid phone number"]);
        exit();
    }
    $sql = "UPDATE customerinfo SET name = ?, phone = ?, email = ?, birthday = ?, address = ?, gender = ?, id_type = ?, more = ? where id_customer = ?";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $name, $phone, $email, $birthday, $address, $gender, $id_type, $more, $id_customer);  
    $stmt->execute(); 
    $stmtres = $conn->prepare("SELECT * FROM customerinfo WHERE id_customer = ?");
    $stmtres->bind_param("i", $id_customer);
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
