<?php
include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    switch ($method) {
        case 'GET':
            // --- ดึงข้อมูล (GET) ---
            if (isset($_GET['id'])) {
                // ถ้ามีการส่ง id มา ดึงข้อมูลแค่คนเดียว
                $stmt = $conn->prepare("SELECT * FROM Customers WHERE customer_id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // ถ้าไม่มีการส่ง id ดึงข้อมูลทั้งหมด
                $stmt = $conn->query("SELECT * FROM Customers");
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode(["message" => "ไม่พบข้อมูล"]);
            }
            break;

        case 'POST':
            // --- เพิ่มข้อมูล (POST) ---
            if (!empty($data['firstName']) && !empty($data['lastName']) && !empty($data['phone']) && !empty($data['username']) && !empty($data['password'])) {
                
                $sql = "INSERT INTO Customers (firstName, lastName, phone, username, password) 
                        VALUES (:firstName, :lastName, :phone, :username, :password)";
                $stmt = $conn->prepare($sql);
                
                $stmt->execute([
                    ':firstName' => $data['firstName'],
                    ':lastName' => $data['lastName'],
                    ':phone' => $data['phone'],
                    ':username' => $data['username'],
                    ':password' => $data['password'] // แนะนำ: ในระบบจริงควรเข้ารหัสผ่านด้วย password_hash()
                ]);

                echo json_encode(["message" => "เพิ่มข้อมูลลูกค้าเรียบร้อยแล้ว", "id" => $conn->lastInsertId()]);
            } else {
                http_response_code(400);
                echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน"]);
            }
            break;

        case 'PUT':
            // --- อัปเดตข้อมูล (PUT) ---
            if (!empty($data['customer_id'])) {
                $sql = "UPDATE Customers 
                        SET firstName = :firstName, lastName = :lastName, phone = :phone, username = :username, password = :password 
                        WHERE customer_id = :customer_id";
                $stmt = $conn->prepare($sql);
                
                $stmt->execute([
                    ':firstName' => $data['firstName'],
                    ':lastName' => $data['lastName'],
                    ':phone' => $data['phone'],
                    ':username' => $data['username'],
                    ':password' => $data['password'],
                    ':customer_id' => $data['customer_id']
                ]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["message" => "อัปเดตข้อมูลสำเร็จ"]);
                } else {
                    echo json_encode(["message" => "ไม่พบข้อมูลที่ต้องอัปเดต หรือข้อมูลไม่มีการเปลี่ยนแปลง"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "กรุณาระบุ customer_id ที่ต้องการอัปเดต"]);
            }
            break;

        case 'DELETE':
            // --- ลบข้อมูล (DELETE) ---
            // สามารถรับ ID ได้ทั้งจาก JSON Body หรือ Query String (?id=...)
            $id = isset($data['customer_id']) ? $data['customer_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
            
            if ($id) {
                $sql = "DELETE FROM Customers WHERE customer_id = :customer_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':customer_id' => $id]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["message" => "ลบข้อมูลสำเร็จ"]);
                } else {
                    echo json_encode(["error" => "ไม่พบข้อมูลที่ต้องการลบ"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "กรุณาระบุ customer_id ที่ต้องการลบ"]);
            }
            break;

        default:
            // หากเรียกด้วย Method อื่นๆ นอกเหนือจากนี้
            http_response_code(405);
            echo json_encode(["error" => "Method Not Allowed"]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>