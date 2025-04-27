<?php
header('Content-Type: application/json');
include "db.php";
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Lỗi kết nối cơ sở dữ liệu"]));
}
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

if (!empty($data["id_user"]) && !empty($data["access_token"])) {
    $id_user = $conn->real_escape_string($data["id_user"]);
    $access_token = $conn->real_escape_string($data["access_token"]);

    $stmtcheck = $conn->prepare("SELECT account.id_role FROM account INNER JOIN users ON account.id_account = users.id_account WHERE users.access_token = ?");
    $stmtcheck->bind_param("s", $access_token);
    $stmtcheck->execute();
    $resultcheck = $stmtcheck->get_result();
    $rowcheck = $resultcheck->fetch_assoc();
    
    if ($rowcheck['id_role'] < 3) {
        echo json_encode(["status" => "failed", "message" => "Tài khoản không có quyền xoá"]);
        exit();
    } else {
        // Bắt lỗi khi xóa người dùng có liên kết với các bảng khác
        $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
        $stmt->bind_param("s", $id_user);
        
        try {
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Delete information successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi khi xoá người dùng"]);
            }
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                echo json_encode(["status" => "error", "message" => "Không thể xoá người dùng do có liên quan đến các bảng khác"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi không xác định khi xoá người dùng"]);
            }
        }
        
        $stmt->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>