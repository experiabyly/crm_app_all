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
    $type = [];
    $id_customer = $conn->real_escape_string($data["id_business"]);

    // Lấy thông tin đơn hàng của khách hàng
    $stmtus = $conn->prepare("
        SELECT * 
        FROM customertype
        WHERE customertype.id_business = ?
    ");
    $stmtus->bind_param("s", $id_customer);
    $stmtus->execute();
    $resus = $stmtus->get_result();
    while ($rowus = $resus->fetch_assoc()) {
        $type[] = $rowus;
    }
    echo json_encode(["status" => "success", "data" => $type, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
