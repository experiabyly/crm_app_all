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

if (!empty($data["year"]) && !empty($data["month"]) && !empty($data["id_business"])) {
    $year = (int) $data["year"];
    $month = (int) $data["month"];
    $id_business = (int) $data["id_business"];
    // Kiểm tra tài khoản trong database
    $satistic = []; // Khởi tạo mảng rỗng
    $total = 0;

    // Sửa câu lệnh SQL: Lọc theo tháng và năm
    $sql = "SELECT SUM(orders.total) AS total FROM orders 
            INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
            WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND account.id_business = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $year, $month, $id_business);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
    $satistic["total in $month/$year"] = $total;

    $sqltoor = "SELECT COUNT(*) AS count FROM orders INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND account.id_business = ?";
    $stmttoor = $conn->prepare($sqltoor);
    $stmttoor->bind_param("iii", $year, $month, $id_business);
    $stmttoor->execute();
    $resulttoor = $stmttoor->get_result();
    $rowtoor = $resulttoor->fetch_assoc();
    $totaltoor = $rowtoor['count'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
    $satistic["total orders in $month/$year"] = $totaltoor;

    $sqltoorye = "SELECT COUNT(*) AS count FROM orders INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account WHERE YEAR(orders.date) = ? AND account.id_business = ?";
    $stmttoorye = $conn->prepare($sqltoorye);
    $stmttoorye->bind_param("ii", $year, $id_business);
    $stmttoorye->execute();
    $resulttoorye = $stmttoorye->get_result();
    $rowtoorye = $resulttoorye->fetch_assoc();
    $totaltoorye = $rowtoorye['count'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
    $satistic["total orders in $year"] = $totaltoorye;

    $stmtye = $conn->prepare("SELECT SUM(orders.total) AS total FROM orders 
            INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
            WHERE YEAR(orders.date) = ? AND account.id_business = ?");
    $stmtye->bind_param("ii", $year, $id_business);
    $stmtye->execute();
    $resultye = $stmtye->get_result();
    $rowye = $resultye->fetch_assoc();
    $satistic["total in $year"] = $rowye['total'] ?? 0;

    $stmtcusmon = $conn->prepare("SELECT orders.id_order, orders.id_customer, customerinfo.name, COUNT(*) AS total_orders
    FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND account.id_business = ?
    GROUP BY id_customer
    ORDER BY total_orders DESC
    LIMIT 1;
    ");
    $stmtcusmon->bind_param("iii", $year, $month, $id_business);
    $stmtcusmon->execute();
    $resultcusmon = $stmtcusmon->get_result();
    $rowcusmon = $resultcusmon->fetch_assoc();
    $satistic["most buy in $month/$year"][] = [
        "id_customer" => $rowcusmon['id_customer'],
        "name" => $rowcusmon['name'],
        "total orders" => $rowcusmon['total_orders']
    ];

    $stmtcus = $conn->prepare("SELECT orders.id_customer, customerinfo.name, COUNT(*) AS total_orders
    FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND account.id_business = ?
    GROUP BY id_customer
    ORDER BY total_orders DESC
    LIMIT 1;
    ");
    $stmtcus->bind_param("ii", $year, $id_business);
    $stmtcus->execute();
    $resultcus = $stmtcus->get_result();
    $rowcus = $resultcus->fetch_assoc();
    $satistic["most buy in $year"][] = [
        "id_customer" => $rowcus['id_customer'],
        "name" => $rowcus['name'],
        "total orders" => $rowcus['total_orders']
    ];

    $stmtpromon = $conn->prepare("SELECT product.id_product, product.name, SUM(ordersdetail.amount) AS total_sell
    FROM product INNER JOIN ordersdetail ON product.id_product = ordersdetail.id_product
    INNER JOIN orders ON orders.id_order = ordersdetail.id_order INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND account.id_business = ?
    GROUP BY id_product
    ORDER BY total_sell DESC
    LIMIT 1;");
    $stmtpromon->bind_param("iii", $year, $month, $id_business);
    $stmtpromon->execute();
    $resultpromon = $stmtpromon->get_result();
    $rowpromon = $resultpromon->fetch_assoc();
    $satistic["most sell in $month/$year"][] = [
        "id_product" => $rowpromon['id_product'],
        "name" => $rowpromon['name'],
        "total sell" => $rowpromon['total_sell']
    ];

    $stmtproye = $conn->prepare("SELECT product.id_product, product.name, SUM(ordersdetail.amount) AS total_sell
    FROM product INNER JOIN ordersdetail ON product.id_product = ordersdetail.id_product
    INNER JOIN orders ON orders.id_order = ordersdetail.id_order INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND account.id_business = ?
    GROUP BY id_product
    ORDER BY total_sell DESC
    LIMIT 1;");
    $stmtproye->bind_param("ii", $year, $id_business);
    $stmtproye->execute();
    $resultproye = $stmtproye->get_result();
    $rowproye = $resultproye->fetch_assoc();
    $satistic["most sell in $year"][] = [
        "id_product" => $rowproye['id_product'],
        "name" => $rowproye['name'],
        "total sell" => $rowproye['total_sell']
    ];

    $stmtallcus = $conn->prepare("SELECT COUNT(*) AS count
    FROM customerinfo 
    WHERE YEAR(customerinfo.joindate) = ? AND MONTH(customerinfo.joindate) <= ? AND customerinfo.id_business = ?");
    $stmtallcus->bind_param("iii", $year, $month, $id_business);
    $stmtallcus->execute();
    $resultallcus = $stmtallcus->get_result();
    $rowallcus = $resultallcus->fetch_assoc();
    $satistic["total customer till $month/$year"] = $rowallcus;

    echo json_encode(["status" => "success", "data" => $satistic, "message" => "Lấy dữ liệu thành công!"]);

    $stmt->close();
} else {
    if (!empty($data["year"]) && !empty($data["id_business"])) {
        $year = (int) $data["year"];
        $id_business = (int) $data["id_business"];
        $satistic = []; // Khởi tạo mảng rỗng
        $total = 0;

        $sqltoorye = "SELECT COUNT(*) AS count FROM orders INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account WHERE YEAR(orders.date) = ? AND account.id_business = ?";
        $stmttoorye = $conn->prepare($sqltoorye);
        $stmttoorye->bind_param("ii", $year, $id_business);
        $stmttoorye->execute();
        $resulttoorye = $stmttoorye->get_result();
        $rowtoorye = $resulttoorye->fetch_assoc();
        $totaltoorye = $rowtoorye['count'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
        $satistic["total orders in $year"] = $totaltoorye;

        $stmtye = $conn->prepare("SELECT SUM(orders.total) AS total FROM orders 
                INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
                WHERE YEAR(orders.date) = ? AND account.id_business = ?");
        $stmtye->bind_param("ii", $year, $id_business);
        $stmtye->execute();
        $resultye = $stmtye->get_result();
        $rowye = $resultye->fetch_assoc();
        $satistic["total in $year"] = $rowye['total'] ?? 0;

        $stmtcus = $conn->prepare("SELECT orders.id_customer, customerinfo.name, COUNT(*) AS total_orders
        FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
        WHERE YEAR(orders.date) = ? AND account.id_business = ?
        GROUP BY id_customer
        ORDER BY total_orders DESC
        LIMIT 1;
        ");
        $stmtcus->bind_param("ii", $year, $id_business);
        $stmtcus->execute();
        $resultcus = $stmtcus->get_result();
        $rowcus = $resultcus->fetch_assoc();
        $satistic["most buy in $year"][] = [
            "id_customer" => $rowcus['id_customer'],
            "name" => $rowcus['name'],
            "total orders" => $rowcus['total_orders']
        ];

        $stmtproye = $conn->prepare("SELECT product.id_product, product.name, SUM(ordersdetail.amount) AS total_sell
        FROM product INNER JOIN ordersdetail ON product.id_product = ordersdetail.id_product
        INNER JOIN orders ON orders.id_order = ordersdetail.id_order INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
        WHERE YEAR(orders.date) = ? AND account.id_business = ?
        GROUP BY id_product
        ORDER BY total_sell DESC
        LIMIT 1;");
        $stmtproye->bind_param("ii", $year, $id_business);
        $stmtproye->execute();
        $resultproye = $stmtproye->get_result();
        $rowproye = $resultproye->fetch_assoc();
        $satistic["most sell in $year"][] = [
            "id_product" => $rowproye['id_product'],
            "name" => $rowproye['name'],
            "total sell" => $rowproye['total_sell']
        ];

        $stmtallcus = $conn->prepare("SELECT COUNT(*) AS count
        FROM customerinfo 
        WHERE YEAR(customerinfo.joindate) = ? AND customerinfo.id_business = ?");
        $stmtallcus->bind_param("ii", $year, $id_business);
        $stmtallcus->execute();
        $resultallcus = $stmtallcus->get_result();
        $rowallcus = $resultallcus->fetch_assoc();
        $satistic["total customer till $year"] = $rowallcus;

        echo json_encode(["status" => "success", "data" => $satistic, "message" => "Lấy dữ liệu thành công!"]);

    } else {
        echo json_encode(["status" => "error", "message" => "Error!"]);
    }
    
}

$conn->close();
?>
