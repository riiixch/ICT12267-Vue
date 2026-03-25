<?php
include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    switch ($method) {
        case 'GET':
            // --- ดึงข้อมูล (GET) ---
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conn->query("SELECT * FROM employees");
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode(["message" => "ไม่พบข้อมูลพนักงาน"]);
            }
            break;

        case 'POST':
            // --- เพิ่มข้อมูล (POST) ---
            // ต้องเช็ค emp_id ด้วยเพราะไม่ได้เป็น AUTO_INCREMENT
            if (!empty($data['emp_id']) && !empty($data['full_name']) && !empty($data['department']) && isset($data['salary'])) {
                
                // กำหนดค่า active ถ้าไม่ได้ส่งมาให้เป็น 1 (TRUE) ตาม Default ของตาราง
                $active = isset($data['active']) ? $data['active'] : 1;

                $sql = "INSERT INTO employees (emp_id, full_name, department, salary, active) 
                        VALUES (:emp_id, :full_name, :department, :salary, :active)";
                $stmt = $conn->prepare($sql);
                
                try {
                    $stmt->execute([
                        ':emp_id' => $data['emp_id'],
                        ':full_name' => $data['full_name'],
                        ':department' => $data['department'],
                        ':salary' => $data['salary'],
                        ':active' => $active
                    ]);
                    echo json_encode(["message" => "เพิ่มข้อมูลพนักงานเรียบร้อยแล้ว", "emp_id" => $data['emp_id']]);
                } catch (PDOException $e) {
                    // ดักจับ error กรณี emp_id ซ้ำ (Duplicate Key)
                    if ($e->getCode() == 23000) {
                        http_response_code(409); // Conflict
                        echo json_encode(["error" => "รหัสพนักงาน (emp_id) นี้มีในระบบแล้ว"]);
                    } else {
                        throw $e;
                    }
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน กรุณาส่ง emp_id, full_name, department และ salary"]);
            }
            break;

        case 'PUT':
            // --- อัปเดตข้อมูล (PUT) ---
            if (!empty($data['emp_id'])) {
                $sql = "UPDATE employees 
                        SET full_name = :full_name, department = :department, salary = :salary, active = :active 
                        WHERE emp_id = :emp_id";
                $stmt = $conn->prepare($sql);
                
                $stmt->execute([
                    ':full_name' => $data['full_name'],
                    ':department' => $data['department'],
                    ':salary' => $data['salary'],
                    ':active' => isset($data['active']) ? $data['active'] : 1, // ถ้าไม่ส่งมาปรับเป็น 1
                    ':emp_id' => $data['emp_id']
                ]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["message" => "อัปเดตข้อมูลพนักงานสำเร็จ"]);
                } else {
                    echo json_encode(["message" => "ไม่พบข้อมูลที่ต้องอัปเดต หรือข้อมูลไม่มีการเปลี่ยนแปลง"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "กรุณาระบุ emp_id ที่ต้องการอัปเดต"]);
            }
            break;

        case 'DELETE':
            // --- ลบข้อมูล (DELETE) ---
            $id = isset($data['emp_id']) ? $data['emp_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
            
            if ($id) {
                $sql = "DELETE FROM employees WHERE emp_id = :emp_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':emp_id' => $id]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["message" => "ลบข้อมูลพนักงานสำเร็จ"]);
                } else {
                    echo json_encode(["error" => "ไม่พบข้อมูลพนักงานที่ต้องการลบ"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "กรุณาระบุ emp_id ที่ต้องการลบ"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method Not Allowed"]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>