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

if (!empty($data["access_token"])) {
    $access_token = $conn->real_escape_string($data["access_token"]);

    // Kiểm tra tài khoản trong database
    $stmt = $conn->prepare("SELECT name, address, id_account FROM users WHERE access_token = ?");
    $stmt->bind_param("s", $access_token);
    $stmt->execute();
    $resuser = $stmt->get_result();
    $rowuser = $resuser->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT email, phone, id_business FROM account WHERE id_account = ?");
    $stmt->bind_param("s", $rowuser['id_account']);
    $stmt->execute();
    $resacc= $stmt->get_result();
    $rowacc = $resacc->fetch_assoc();
    $stmt->close();

    $userinfo = array_merge($rowuser, $rowacc);
    echo json_encode(["status" => "success", "data" => $userinfo, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
