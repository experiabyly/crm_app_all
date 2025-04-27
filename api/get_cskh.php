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
    $customers = [];

    // Truy vấn danh sách khách hàng và user liên quan
    $stmt = $conn->prepare("SELECT c.id_customer, c.name AS customer_name, u.id_user, u.name AS user_name 
                            FROM customerinfo c 
                            INNER JOIN cskh cs ON c.id_customer = cs.id_customer 
                            INNER JOIN users u ON cs.id_user = u.id_user
                            WHERE c.id_business = ?");
    $stmt->bind_param("s", $id_business);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $id_customer = $row['id_customer'];
        $customer_name = $row['customer_name'];
        $id_user = $row['id_user'];
        $user_name = $row['user_name'];

        // Nếu khách hàng chưa có trong danh sách, thêm vào
        if (!isset($customers[$id_customer])) {
            $customers[$id_customer] = [
                "id_customer" => $id_customer,
                "customer name" => $customer_name,
                "users" => []
            ];
        }

        // Thêm user vào danh sách users
        $customers[$id_customer]["users"][] = [
            "id_user" => $id_user,
            "user name" => $user_name
        ];
    }

    $stmt->close();
    echo json_encode(["status" => "success", "data" => array_values($customers), "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
