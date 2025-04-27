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

if (!empty($data["id_work"])) {
    $id_work = $conn->real_escape_string($data["id_work"]);
    $stmtupdate = $conn->prepare("
        UPDATE work
        SET status = 1
        WHERE id_work = ?
    ");

    $stmtupdate->bind_param("s", $id_work);
    $stmtupdate->execute();
    $stmt->close();
    echo json_encode(["status" => "success", "data" => $res, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
