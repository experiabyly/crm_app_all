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
    $customer = []; 
    // Kiểm tra tài khoản trong database
    $stmt = $conn->prepare("SELECT * FROM customerinfo INNER JOIN cskh ON customerinfo.id_customer = cskh.id_customer WHERE customerinfo.id_business = ?");
    $stmt->bind_param("s", $id_business);
    $stmt->execute();
    $resuser = $stmt->get_result();
    if ($resuser->num_rows > 0) {
        while ($row = $resuser->fetch_assoc()) {
            $stmthis = $conn->prepare("SELECT date, note FROM customercarehistory WHERE id_cskh  = ?");
            $stmthis->bind_param("i", $row['id_cskh']);
            $stmthis->execute();
            $reshis = $stmthis->get_result();
            if ($reshis->num_rows > 0)  {
                $rowhis = $reshis->fetch_assoc();

                $stmtus = $conn->prepare("SELECT name FROM users WHERE id_user  = ?");
                $stmtus->bind_param("i", $row['id_user']);
                $stmtus->execute();
                $resus = $stmtus->get_result();
                $rowus = $resus->fetch_assoc();
    
                $stmtcus = $conn->prepare("SELECT name FROM customerinfo WHERE id_customer  = ?");
                $stmtcus->bind_param("i", $row['id_customer']);
                $stmtcus->execute();
                $rescus = $stmtcus->get_result();
                $rowcus = $rescus->fetch_assoc();
    
                $customer[] = [
                    "id_customer" => $row['id_customer'],
                    "customer name" => $rowcus['name'],
                    "id_user" => $row['id_user'],
                    "user name" => $rowus['name'],
                    "date" => $rowhis['date'],
                    "note" => $rowhis['note'],
                ];
            }
            
        }
    }
    $stmt->close();
    echo json_encode(["status" => "success", "data" => $customer, "message" => "Get information successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error!"]);
}

$conn->close();
?>
