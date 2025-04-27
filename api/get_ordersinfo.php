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

if (!empty($data["id_order"])) {
    $customer = []; 
    $id_order = $conn->real_escape_string($data["id_order"]);

    // Lấy thông tin khách hàng
    $stmtor = $conn->prepare("SELECT customerinfo.name FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer WHERE orders.id_order = ?");
    $stmtor->bind_param("s", $id_order);
    $stmtor->execute();
    $resor = $stmtor->get_result();
    if ($rowor = $resor->fetch_assoc()) {
        $customer['customer_name'] = $rowor['name'];
    }

    // Lấy thông tin người dùng
    $stmtus = $conn->prepare("SELECT users.name FROM orders INNER JOIN users ON orders.id_user = users.id_user WHERE orders.id_order = ?");
    $stmtus->bind_param("s", $id_order);
    $stmtus->execute();
    $resus = $stmtus->get_result();
    if ($rowus = $resus->fetch_assoc()) {
        $customer['user_name'] = $rowus['name'];
    }

    // Lấy danh sách sản phẩm trong đơn hàng
    $customer['detail'] = []; // Khởi tạo mảng detail
    $stmt = $conn->prepare("SELECT id_product, amount FROM ordersdetail WHERE id_order = ?");
    $stmt->bind_param("s", $id_order);
    $stmt->execute();
    $resuser = $stmt->get_result();

    while ($row = $resuser->fetch_assoc()) {
        $more_details = [];

        // Lấy thông tin sản phẩm
        $stmt_custom = $conn->prepare("SELECT name FROM product WHERE id_product = ?");
        $stmt_custom->bind_param("s", $row['id_product']);
        $stmt_custom->execute();
        $res_custom = $stmt_custom->get_result();
        $custom = $res_custom->fetch_assoc();

        // Thêm vào danh sách sản phẩm
        $customer['detail'][] = [
            "id_product" => $row['id_product'],
            "name" => $custom['name'],
            "amount" => $row['amount'],
        ];
    }

    // Lấy tổng tiền đơn hàng
    $stmtto = $conn->prepare("SELECT total, date FROM orders WHERE id_order = ?");
    $stmtto->bind_param("s", $id_order);
    $stmtto->execute();
    $resto = $stmtto->get_result();
    $rowto = $resto->fetch_assoc();
    $customer['total'] = $rowto['total'];
    $customer['date'] = $rowto['date'];
    

    $stmt->close();
    echo json_encode(["status" => "success", "data" => $customer, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
