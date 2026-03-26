<?php
include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

try {
    if (!empty($data['firstName']) && !empty($data['lastName']) && !empty($data['phone']) && !empty($data['username']) && !empty($data['password'])) {
        
        // --- ตรวจสอบว่ามี username นี้อยู่แล้วหรือไม่ ---
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM Customers WHERE username = :username");
        $checkStmt->execute([':username' => $data['username']]);
        if ($checkStmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(["error" => "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว"]);
            exit;
        }

        // --- เพิ่มข้อมูลการลงทะเบียน ---
        $sql = "INSERT INTO Customers (firstName, lastName, phone, username, password) 
                VALUES (:firstName, :lastName, :phone, :username, :password)";
        $stmt = $conn->prepare($sql);
        
        $stmt->execute([
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':phone' => $data['phone'],
            ':username' => $data['username'],
            ':password' => $data['password'] // หมายเหตุ: ในระบบจริงควรใช้ password_hash()
        ]);

        echo json_encode([
            "success" => true,
            "message" => "ลงทะเบียนสำเร็จเรียบร้อยแล้ว", 
            "customer_id" => $conn->lastInsertId()
        ]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
