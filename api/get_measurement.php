<?php
header("Content-Type: application/json");
include "db.php";

$businesstype = []; // Khởi tạo mảng rỗng để tránh lỗi Undefined variable

$sql = "SELECT * FROM measurement";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $businesstype[] = $row; // Thêm từng user vào mảng
    }
}

// Chỉ in JSON một lần, tránh lặp dữ liệu
echo json_encode(["status" => "success", "data" => $businesstype]);

$conn->close();
?>
