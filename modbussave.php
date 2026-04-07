<?php
header("Content-Type: application/json");

// Koneksi Database
$host     = "unkown";
$db_user  = "unkown"; 
$db_pass  = "unkown";     
$db_name  = "unkown"; 

// Simpan apapun yang dikirim ke file log.txt untuk dicek
file_put_contents('log_debug.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database Error"]));
}

$json_raw = file_get_contents("php://input");
$data = json_decode($json_raw, true);

if (!empty($data)) {
    // RUTX11 mengirim data Modbus biasanya dalam bentuk array atau objek data
    foreach ($data as $item) {
        $deviceId = $item['device_id'] ?? 'PLC_01';
        
        // Teltonika mengirim setiap register sebagai elemen
        foreach ($item['data'] as $register) {
            $regName  = $register['name']; // Nama register yang disetting di RUTX11
            $regValue = $register['value'];
            
            $stmt = $conn->prepare("INSERT INTO modbus_logs (device_id, register_name, register_value) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $deviceId, $regName, $regValue);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Empty data"]);
}

$conn->close();
?>