<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Kiểm tra đầu vào hợp lệ
if (!empty($data["id_order"]) && !empty($data["id_product"]) && !empty($data["amount"])) {
    $id_order = intval($data["id_order"]);
    $id_product = intval($data["id_product"]);
    $amount = trim($data["amount"]);
    $sql = "INSERT INTO ordersdetail (id_order, id_product, amount) VALUES (?, ?, ?)";   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_order, $id_product, $amount);  
    $stmt->execute(); 

    $lastid = $conn->insert_id;
    $stmtres = $conn->prepare("SELECT * FROM ordersdetail WHERE id_ordersdetail = ?");
    $stmtres->bind_param("i", $lastid);
    $stmtres->execute();
    $resuser = $stmtres->get_result();
    $rowuser = $resuser->fetch_assoc();

    $sqlto = "SELECT price from product WHERE id_product = ?";   
    $stmtto = $conn->prepare($sqlto);
    $stmtto->bind_param("i", $rowuser['id_product']);  
    $stmtto->execute(); 
    $resto = $stmtto->get_result();
    $rowto = $resto->fetch_assoc();
    $totalprice = $rowto['price'] * $rowuser['amount'];

    $sqlor = "SELECT total from orders WHERE id_order = ?";   
    $stmtor = $conn->prepare($sqlor);
    $stmtor->bind_param("i", $rowuser['id_order']);  
    $stmtor->execute(); 
    $resor = $stmtor->get_result();
    $rowor = $resor->fetch_assoc();
    $total = $rowor['total'] + $totalprice;

    $sqlup = "UPDATE orders SET total = ? WHERE id_order = ?";   
    $stmtup = $conn->prepare($sqlup);
    $stmtup->bind_param("si", $total, $rowuser['id_order']);  
    $stmtup->execute(); 

    echo json_encode(["status" => "success", "data" => $rowuser, "message" => "Thêm thành công"]);
    $stmtres->close();
    $stmt->close();        
    
    

} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
