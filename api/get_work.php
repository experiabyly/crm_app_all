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
        SET status = CASE
            WHEN (NOW() + INTERVAL 7 HOUR) > duedate THEN 3
            ELSE 1
        END
        WHERE id_work = ?
    ");

    $stmtupdate->bind_param("s", $id_work);

    if ($stmtupdate->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Cập nhật thành công work id = $id_work"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Cập nhật thất bại: " . $stmtupdate->error
        ]);
    }

    $stmtupdate->close();
} else {
    echo json_encode(["status" => "error", "message" => "Thiếu id_work"]);
}

$conn->close();
?>
