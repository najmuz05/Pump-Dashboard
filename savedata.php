<?php
// Pengaturan Koneksi Database
$host     = "unkown";
$db_user  = "unkown"; 
$db_pass  = "unkown";     
$db_name  = "unkown"; 

header("Content-Type: application/json");

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database Error"]));
}

// 1. Ambil payload mentah
$json_raw = file_get_contents("php://input");
$payload = json_decode($json_raw, true);

if (isset($payload['Pump_Sensor'])) {
    foreach ($payload['Pump_Sensor'] as $device) {
        $mac  = $device['mac'];
        $name = $device['name'];
        //$rssi = $device['rssi'];

        // 2. Karena isi 'data' adalah string JSON, kita harus decode lagi
        $sensorData = json_decode($device['data'], true);

        // 3. Ambil nilai spesifik dari hasil decode kedua
        $current    = $sensorData['current'] ?? 0;
        $freq     = $sensorData['frequency'] ?? 0;
        $volt    = $sensorData['voltage'] ?? 0;
        $temp     = $sensorData['temperature'] ?? 0;
        $vibration  = $sensorData['vibration'] ?? 0;
        $flow = $sensorData['flow'] ?? 0;

        // 4. Simpan ke database
        // Pastikan tabel eye_sensor_data sudah memiliki kolom-kolom ini
        $sql  = "INSERT INTO pump_data (DEVICE_NAME, MAC, CURRENT, FREQUENCY, VOLTAGE, TEMPERATURE, VIBRATION, FLOW) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiddii", $name, $mac, $current, $freq, $volt, $temp, $vibration, $flow);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "device" => $name]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        $stmt->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Format Pump Sensor tidak ditemukan"]);
}

$conn->close();
?>