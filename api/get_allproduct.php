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

if (!empty($data["id_business"])) {
    $id_business = $conn->real_escape_string($data["id_business"]);
    $product = []; 
    // Kiểm tra tài khoản trong database
    $stmt = $conn->prepare("SELECT * FROM product WHERE id_business = ?");
    $stmt->bind_param("s", $id_business);
    $stmt->execute();
    $resuser = $stmt->get_result();
    if ($resuser->num_rows > 0) {
        while ($row = $resuser->fetch_assoc()) {
            $stmtmes = $conn->prepare("SELECT name, description FROM measurement WHERE id_measurement = ?");
            $stmtmes->bind_param("s", $row['id_measurement']);
            $stmtmes->execute();
            $resmes = $stmtmes->get_result();
            $row['measurement'] = $resmes->fetch_assoc();
            unset($row['id_measurement']);
            $product[] = $row; // Thêm từng user vào mảng
        }
    }
    $stmt->close();
    echo json_encode(["status" => "success", "data" => $product, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
