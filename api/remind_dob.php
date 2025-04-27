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

if (!empty($data["id_user"])) {
    $respones = [];
    $id_user = $conn->real_escape_string($data["id_user"]);
    
    // Kiểm tra tài khoản trong database
    $stmt = $conn->prepare(
        "SELECT customerinfo.id_customer, customerinfo.name, customerinfo.birthday
        FROM cskh INNER JOIN customerinfo ON cskh.id_customer = customerinfo.id_customer
        WHERE cskh.id_user = ? AND MONTH(customerinfo.birthday) = MONTH(CURDATE())"
    );
    $stmt->bind_param("s", $id_user);
    $stmt->execute();
    $resuser = $stmt->get_result();
    while ($row = $resuser->fetch_assoc()) {
        $respones['birthday in month'][] = [
            "id_customer" => $row["id_customer"],
            "name" => $row['name'],
            "birthday" => $row['birthday']
        ];
    }

    $stmttoday = $conn->prepare("SELECT customerinfo.id_customer, customerinfo.name, customerinfo.birthday
        FROM cskh INNER JOIN customerinfo ON cskh.id_customer = customerinfo.id_customer
        WHERE cskh.id_user = ? AND MONTH(customerinfo.birthday) = MONTH(CURDATE()) AND DAY(customerinfo.birthday) = DAY(CURDATE())");
    $stmttoday->bind_param("s", $id_user);
    $stmttoday->execute();
    $restoday = $stmttoday->get_result();
    while ($row = $restoday->fetch_assoc()) {
        $respones['birthday today'][] = [
            "id_customer" => $row["id_customer"],
            "name" => $row['name'],
            "birthday" => $row['birthday']
        ];
    }

    $stmtnext = $conn->prepare("SELECT customerinfo.id_customer, customerinfo.name, customerinfo.birthday
        FROM cskh INNER JOIN customerinfo ON cskh.id_customer = customerinfo.id_customer
        WHERE cskh.id_user = ? AND (MONTH(customerinfo.birthday) - 1) = MONTH(CURDATE()) OR (MONTH(customerinfo.birthday) = 1 AND MONTH(CURDATE()) = 12)");
    $stmtnext->bind_param("s", $id_user);
    $stmtnext->execute();
    $restnext = $stmtnext->get_result();
    while ($row = $restnext->fetch_assoc()) {
        $respones['birthday next month'][] = [
            "id_customer" => $row["id_customer"],
            "name" => $row['name'],
            "birthday" => $row['birthday']
        ];
    }

    $stmt->close();
    echo json_encode(["status" => "success", "data" => $respones, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
