<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_user"]) && !empty($data["id_customer"])) {
    
    $id_user = intval($data["id_user"]);
    $id_customer = intval($data["id_customer"]);
    $checkStmt = $conn->prepare("SELECT * FROM cskh WHERE id_user = ? AND id_customer = ?");
    $checkStmt->bind_param("ii", $id_user, $id_customer);
    $checkStmt->execute();
    $checkStmt->store_result();
    $rowuser;
    if ($checkStmt->num_rows > 0) {
        $rescheck = $checkStmt->get_result();
        $rowcheck = $rescheck->fetch_assoc();
        $stmt = $conn->prepare("UPDATE cskh SET id_user = ?, id_customer = ? WHERE id_cskh = ?");
        $stmt->bind_param("iii", $id_user, $id_customer, $rowcheck["id_cskh"]);
        $stmt->execute();
        $stmtres = $conn->prepare("SELECT * FROM cskh WHERE id_cskh = ?");
        $stmtres->bind_param("i", $rowcheck["id_cskh"]);
        $stmtres->execute();
        $resuser = $stmtres->get_result();
        $rowuser = $resuser->fetch_assoc();
    } else {
        $sql = "INSERT INTO cskh (id_user, id_customer) VALUES (?, ?)";   
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_user, $id_customer);  
        $stmt->execute(); 
        $lastid = $conn->insert_id;
        $stmtres = $conn->prepare("SELECT * FROM cskh WHERE id_cskh = ?");
        $stmtres->bind_param("i", $lastid);
        $stmtres->execute();
        $resuser = $stmtres->get_result();
        $rowuser = $resuser->fetch_assoc();
    }

    
    echo json_encode(["status" => "success", "data" => $rowuser, "message" => "Thêm thành công"]);
    $stmtres->close();
    $stmt->close();        
    
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
