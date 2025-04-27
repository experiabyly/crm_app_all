<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_user"]) && !empty($data["id_customer"]) && !empty($data["date"]) && !empty($data["note"]) && !empty($data["id_type"])) {
    
    $id_user = intval($data["id_user"]);
    $id_customer = intval($data["id_customer"]);
    $date = trim($data["date"]);
    $note = trim($data["note"]);
    $id_type = intval($data["id_type"]);

    // Kiểm tra xem có bản ghi trong cskh không
    $checkStmt = $conn->prepare("SELECT id_cskh FROM cskh WHERE id_user = ? AND id_customer = ?");
    $checkStmt->bind_param("ii", $id_user, $id_customer);
    $checkStmt->execute();
    $rescheck = $checkStmt->get_result();

    if ($rescheck->num_rows > 0) {
        $rowcheck = $rescheck->fetch_assoc();
        $id_cskh = $rowcheck['id_cskh'];

        // Chèn lịch sử chăm sóc khách hàng
        $stmt = $conn->prepare("INSERT INTO customercarehistory(id_cskh, date, note, id_type) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("issi", $id_cskh, $date, $note, $id_type);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Thêm thành công"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi khi thêm lịch sử"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Sai ID khách hoặc ID nhân viên"]);
    }

    $checkStmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Dữ liệu đầu vào không hợp lệ"]);
}

$conn->close();
?>
