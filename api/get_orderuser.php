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
    $count = 0;
    $all_total = 0;
    $customer = ["orders" => []]; 
    $id_user = $conn->real_escape_string($data["id_user"]);

    // Lấy thông tin đơn hàng của khách hàng
    $stmtus = $conn->prepare("
        SELECT customerinfo.name, orders.date, orders.total, orders.id_order 
        FROM orders 
        INNER JOIN users ON orders.id_user = users.id_user 
        INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer
        WHERE users.id_user = ?
    ");
    $stmtus->bind_param("s", $id_user);
    $stmtus->execute();
    $resus = $stmtus->get_result();

    while ($rowus = $resus->fetch_assoc()) {
        $count++;
        $all_total += $rowus['total'];
        $customer['orders'][] = [
            'id_order' => $rowus['id_order'],
            'customer_name' => $rowus['name'],
            'date' => $rowus['date'],
            'total' => $rowus['total']
        ];
    }
    
    $customer['count'] = $count;
    $customer['all_total'] = $all_total;

    echo json_encode(["status" => "success", "data" => $customer, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
