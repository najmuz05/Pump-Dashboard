<?php
include 'config.php';

$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

$filename = "Sensor_Data_" . date('Ymd_His') . ".xls"; // Gunakan .xls

// Header agar dibaca sebagai Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil Data
$st = date('Y-m-d H:i:s', strtotime($start . ' -7 hours'));
$en = date('Y-m-d H:i:s', strtotime($end . ' -7 hours'));

$sql = "SELECT received_at, device_name, temperature, humidity, voltage, moving_state 
        FROM eye_sensor_data 
        WHERE received_at BETWEEN '$st' AND '$en' 
        ORDER BY received_at DESC";
$result = $conn->query($sql);
?>

<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>Waktu (UTC)</th>
            <th>Nama Perangkat</th>
            <th>Suhu (°C)</th>
            <th>Kelembapan (%)</th>
            <th>Tegangan (mV)</th>
            <th>Status Gerak</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['received_at']; ?></td>
            <td><?php echo $row['device_name']; ?></td>
            <td><?php echo $row['temperature']; ?></td>
            <td><?php echo $row['humidity']; ?></td>
            <td><?php echo $row['voltage']; ?></td>
            <td><?php echo ($row['moving_state'] == 1 ? 'MOVING' : 'IDLE'); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>



<?php die();
?>
kalo export ke CSV
<?php
// Koneksi ke database
include 'config.php'; // Pastikan path ke koneksi database benar

$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

// 1. Tentukan nama file
$filename = "Sensor_Data_" . date('Ymd_His') . ".csv";

// 2. Header agar browser mengenali ini sebagai file Excel/CSV
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: application/csv;");

// 3. Ambil data berdasarkan filter waktu yang sama dengan dashboard
$sql = "SELECT received_at, device_name, temperature, humidity, voltage, moving_state 
        FROM eye_sensor_data WHERE 1=1";

if ($start != '' && $end != '') {
    // Sesuaikan GMT+7 ke UTC untuk database jika perlu
    $st = date('Y-m-d H:i:s', strtotime($start . ' -7 hours'));
    $en = date('Y-m-d H:i:s', strtotime($end . ' -7 hours'));
    $sql .= " AND received_at BETWEEN '$st' AND '$en'";
}

$sql .= " ORDER BY received_at DESC";
$result = $conn->query($sql);

// 4. Buka "output stream" untuk menulis CSV
$file = fopen('php://output', 'w');

// Set judul kolom di baris pertama Excel
fputcsv($file, ['Waktu (UTC)', 'Nama Perangkat', 'Suhu (C)', 'Kelembapan (%)', 'Tegangan (mV)', 'Status Gerak']);

// 5. Masukkan data dari database ke dalam file
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Mengubah moving_state angka ke teks agar mudah dibaca di Excel
        $row['moving_state'] = ($row['moving_state'] == 1) ? 'MOVING' : 'IDLE';
        fputcsv($file, $row);
    }
}

fclose($file);
exit;
?>