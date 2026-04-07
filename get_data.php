<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
try {
    header("Content-Type: application/json");
    $host     = "unkown";
    $db_user  = "unkown"; 
    $db_pass  = "unkown";     
    $db_name  = "unkown"; 

    $limit  = isset($_GET['lim']) ? (int)$_GET['lim'] : 100;
    $offset = isset($_GET['off']) ? (int)$_GET['off'] : 0;
    $start  = isset($_GET['start']) ? $_GET['start'] : '';
    $end    = isset($_GET['end']) ? $_GET['end'] : '';




    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
    $query = "SELECT *, DATE_ADD(timestamp, INTERVAL 7 HOUR) as received_at FROM pump_data WHERE 1=1";
    


        // 1. Konversi input user (GMT+7) ke UTC untuk Query
    if (!empty($start) && !empty($end)) {
        $tz_user = new DateTimeZone('Asia/Jakarta');
        $tz_utc  = new DateTimeZone('UTC');
        
        $dt_start = new DateTime($start, $tz_user);
        $dt_start->setTimezone($tz_utc);
        $start_utc = $dt_start->format('Y-m-d H:i:s');

        $dt_end = new DateTime($end, $tz_user);
        $dt_end->setTimezone($tz_utc);
        $end_utc = $dt_end->format('Y-m-d H:i:s');

        $query .= " AND timestamp BETWEEN '$start_utc' AND '$end_utc'";
        
        // --- LOGIKA SAMPLING OTOMATIS ---
        $diff = strtotime($end_utc) - strtotime($start_utc);
        $OneHalfHours = 1800 + 3600;
        $FiveHours = 5 * 3600;
        $TwelveHours = 12 * 3600;
        $oneDay = 24 * 3600;

        if ($diff > $oneDay) {
            // Jika lebih dari 24 jam, ambil 1 data setiap 90 baris
            $query .= " AND ID % 35 = 0";
        }
        elseif ($diff > $TwelveHours) {
            // Jika lebih dari 12 jam, ambil 1 data setiap 50 baris
            $query .= " AND ID % 21 = 0";
        }
        elseif ($diff > $FiveHours) {
            // Jika lebih dari 5 jam, ambil 1 data setiap 20 baris
            $query .= " AND ID % 7 = 0";
        } 
        elseif ($diff > $OneHalfHours) {
            // Jika lebih dari 1.5 jam, ambil 1 data setiap 5 baris
            $query .= " AND ID % 2 = 0";
        }
    }

    $query .= " ORDER BY received_at DESC LIMIT $limit OFFSET $offset";


    $result = $conn->query($query);
    
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;

    }

    // Balik urutan untuk grafik (agar kiri ke kanan)
    echo json_encode(array_reverse($data));
    $conn->close();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>