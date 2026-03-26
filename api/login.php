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
    if (!empty($data['username']) && !empty($data['password'])) {
        
        // --- ตรวจสอบข้อมูลผู้ใช้ ---
        $stmt = $conn->prepare("SELECT customer_id, firstName, lastName, phone, username FROM Customers WHERE username = :username AND password = :password");
        $stmt->execute([
            ':username' => $data['username'],
            ':password' => $data['password']
        ]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                "success" => true,
                "message" => "เข้าสู่ระบบสำเร็จ",
                "user" => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
