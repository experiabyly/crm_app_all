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

if (!empty($data["id_noti"])) {
    $id_noti = $conn->real_escape_string($data["id_noti"]);
    $stmtupdate = $conn->prepare("
        UPDATE notification
        SET readstatus = 1
        WHERE id_noti = ?
    ");

    $stmtupdate->bind_param("s", $id_noti);
    $stmtupdate->execute();
    $stmtupdate->close();
    echo json_encode(["status" => "success", "message" => "Change information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
