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

if (!empty($data["year"]) && !empty($data["month"]) && !empty($data["id_user"])) {
    $year = (int) $data["year"];
    $month = (int) $data["month"];
    $id_user = (int) $data["id_user"];
    // Kiểm tra tài khoản trong database
    $satistic = []; // Khởi tạo mảng rỗng
    $total = 0;

    // Sửa câu lệnh SQL: Lọc theo tháng và năm
    $sql = "SELECT SUM(orders.total) AS total FROM orders 
            INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
            WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND orders.id_user = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $year, $month, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
    $satistic["total in $month/$year"] = $total;

    $sqltoor = "SELECT COUNT(*) AS count FROM orders INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND orders.id_user = ?";
    $stmttoor = $conn->prepare($sqltoor);
    $stmttoor->bind_param("iii", $year, $month, $id_user);
    $stmttoor->execute();
    $resulttoor = $stmttoor->get_result();
    $rowtoor = $resulttoor->fetch_assoc();
    $totaltoor = $rowtoor['count'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
    $satistic["total orders in $month/$year"] = $totaltoor;

    $sqltoorye = "SELECT COUNT(*) AS count FROM orders INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account WHERE YEAR(orders.date) = ? AND orders.id_user = ?";
    $stmttoorye = $conn->prepare($sqltoorye);
    $stmttoorye->bind_param("ii", $year, $id_user);
    $stmttoorye->execute();
    $resulttoorye = $stmttoorye->get_result();
    $rowtoorye = $resulttoorye->fetch_assoc();
    $totaltoorye = $rowtoorye['count'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
    $satistic["total orders in $year"] = $totaltoorye;

    $stmtye = $conn->prepare("SELECT SUM(orders.total) AS total FROM orders 
            INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
            WHERE YEAR(orders.date) = ? AND orders.id_user = ?");
    $stmtye->bind_param("ii", $year, $id_user);
    $stmtye->execute();
    $resultye = $stmtye->get_result();
    $rowye = $resultye->fetch_assoc();
    $satistic["total in $year"] = $rowye['total'] ?? 0;

    $stmtcusmon = $conn->prepare("SELECT orders.id_order, orders.id_customer, customerinfo.name, COUNT(*) AS total_orders
    FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND orders.id_user = ?
    GROUP BY id_customer
    ORDER BY total_orders DESC
    LIMIT 1;
    ");
    $stmtcusmon->bind_param("iii", $year, $month, $id_user);
    $stmtcusmon->execute();
    $resultcusmon = $stmtcusmon->get_result();
    $rowcusmon = $resultcusmon->fetch_assoc();
    if ($rowcusmon) {
        $satistic["most buy in $month/$year"][] = [
            "id_customer" => $rowcusmon['id_customer'],
            "name" => $rowcusmon['name'],
            "total orders" => $rowcusmon['total_orders']
        ];
    } else {
        $satistic["most buy in $month/$year"][] = [
            "id_customer" => null,
            "name" => null,
            "total orders" => null
        ];
    }
    

    $stmtcus = $conn->prepare("SELECT orders.id_customer, customerinfo.name, COUNT(*) AS total_orders
    FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND orders.id_user = ?
    GROUP BY id_customer
    ORDER BY total_orders DESC
    LIMIT 1;
    ");
    $stmtcus->bind_param("ii", $year, $id_user);
    $stmtcus->execute();
    $resultcus = $stmtcus->get_result();
    $rowcus = $resultcus->fetch_assoc();
    if ($rowcus) {
        $satistic["most buy in $year"][] = [
            "id_customer" => $rowcus['id_customer'],
            "name" => $rowcus['name'],
            "total orders" => $rowcus['total_orders']
        ];
    } else {
        $satistic["most buy in $year"][] = [
            "id_customer" => null,
            "name" => null,
            "total orders" => null
        ];
    }
    

    $stmtpromon = $conn->prepare("SELECT product.id_product, product.name, SUM(ordersdetail.amount) AS total_sell
    FROM product INNER JOIN ordersdetail ON product.id_product = ordersdetail.id_product
    INNER JOIN orders ON orders.id_order = ordersdetail.id_order INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND MONTH(orders.date) = ? AND orders.id_user = ?
    GROUP BY id_product
    ORDER BY total_sell DESC
    LIMIT 1;");
    $stmtpromon->bind_param("iii", $year, $month, $id_user);
    $stmtpromon->execute();
    $resultpromon = $stmtpromon->get_result();
    $rowpromon = $resultpromon->fetch_assoc();
    if ($rowpromon ) {
        $satistic["most sell in $month/$year"][] = [
            "id_product" => $rowpromon['id_product'],
            "name" => $rowpromon['name'],
            "total sell" => $rowpromon['total_sell']
        ];
    } else {
        $satistic["most sell in $month/$year"][] = [
            "id_product" => null,
            "name" => null,
            "total sell" => null
        ];
    }
    

    $stmtproye = $conn->prepare("SELECT product.id_product, product.name, SUM(ordersdetail.amount) AS total_sell
    FROM product INNER JOIN ordersdetail ON product.id_product = ordersdetail.id_product
    INNER JOIN orders ON orders.id_order = ordersdetail.id_order INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
    WHERE YEAR(orders.date) = ? AND orders.id_user = ?
    GROUP BY id_product
    ORDER BY total_sell DESC
    LIMIT 1;");
    $stmtproye->bind_param("ii", $year, $id_user);
    $stmtproye->execute();
    $resultproye = $stmtproye->get_result();
    $rowproye = $resultproye->fetch_assoc();
    if ($rowproye) {
        $satistic["most sell in $year"][] = [
            "id_product" => $rowproye['id_product'],
            "name" => $rowproye['name'],
            "total sell" => $rowproye['total_sell']
        ];
    } else {
        $satistic["most sell in $year"][] = [
            "id_product" => null,
            "name" => null,
            "total sell" => null
        ];
    }
    
    echo json_encode(["status" => "success", "data" => $satistic, "message" => "Lấy dữ liệu thành công!"]);

    $stmt->close();
} else {
    if (!empty($data["year"]) && !empty($data["id_user"])) {
        $year = (int) $data["year"];
        $id_user = (int) $data["id_user"];
        $satistic = []; // Khởi tạo mảng rỗng
        $total = 0;

        $sqltoorye = "SELECT COUNT(*) AS count FROM orders INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account WHERE YEAR(orders.date) = ? AND orders.id_user = ?";
        $stmttoorye = $conn->prepare($sqltoorye);
        $stmttoorye->bind_param("ii", $year, $id_user);
        $stmttoorye->execute();
        $resulttoorye = $stmttoorye->get_result();
        $rowtoorye = $resulttoorye->fetch_assoc();
        $totaltoorye = $rowtoorye['count'] ?? 0; // Nếu không có đơn hàng nào, mặc định là 0
        $satistic["total orders in $year"] = $totaltoorye;

        $stmtye = $conn->prepare("SELECT SUM(orders.total) AS total FROM orders 
                INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
                WHERE YEAR(orders.date) = ? AND orders.id_user = ?");
        $stmtye->bind_param("ii", $year, $id_user);
        $stmtye->execute();
        $resultye = $stmtye->get_result();
        $rowye = $resultye->fetch_assoc();
        $satistic["total in $year"] = $rowye['total'] ?? 0;

        $stmtcus = $conn->prepare("SELECT orders.id_customer, customerinfo.name, COUNT(*) AS total_orders
        FROM orders INNER JOIN customerinfo ON orders.id_customer = customerinfo.id_customer INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
        WHERE YEAR(orders.date) = ? AND orders.id_user = ?
        GROUP BY id_customer
        ORDER BY total_orders DESC
        LIMIT 1;
        ");
        $stmtcus->bind_param("ii", $year, $id_user);
        $stmtcus->execute();
        $resultcus = $stmtcus->get_result();
        $rowcus = $resultcus->fetch_assoc();
        if ($rowcus) {
            $satistic["most buy in $year"][] = [
                "id_customer" => $rowcus['id_customer'],
                "name" => $rowcus['name'],
                "total orders" => $rowcus['total_orders']
            ];
        } else {
            $satistic["most buy in $year"][] = [
                "id_customer" => null,
                "name" => null,
                "total orders" => null
            ];
        }

        $stmtproye = $conn->prepare("SELECT product.id_product, product.name, SUM(ordersdetail.amount) AS total_sell
        FROM product INNER JOIN ordersdetail ON product.id_product = ordersdetail.id_product
        INNER JOIN orders ON orders.id_order = ordersdetail.id_order INNER JOIN users ON orders.id_user = users.id_user INNER JOIN account ON users.id_account = account.id_account
        WHERE YEAR(orders.date) = ? AND orders.id_user = ?
        GROUP BY id_product
        ORDER BY total_sell DESC
        LIMIT 1;");
        $stmtproye->bind_param("ii", $year, $id_user);
        $stmtproye->execute();
        $resultproye = $stmtproye->get_result();
        $rowproye = $resultproye->fetch_assoc();
        if ($rowproye) {
            $satistic["most sell in $year"][] = [
                "id_product" => $rowproye['id_product'],
                "name" => $rowproye['name'],
                "total sell" => $rowproye['total_sell']
            ];
        } else {
            $satistic["most sell in $year"][] = [
                "id_product" => null,
                "name" => null,
                "total sell" => null
            ];
        }
        

        echo json_encode(["status" => "success", "data" => $satistic, "message" => "Lấy dữ liệu thành công!"]);

    } else {
        echo json_encode(["status" => "error", "message" => "Error!"]);
    }
    
}

$conn->close();
?>
