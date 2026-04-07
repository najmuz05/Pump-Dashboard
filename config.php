<?php
header("Content-Type: application/json");
$host     = "unkown";
$db_user  = "unkown"; 
$db_pass  = "unkown";     
$db_name  = "unkown"; 

// Membuat koneksi menggunakan MySQLi
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset ke UTF-8 agar karakter khusus sensor terbaca benar
$conn->set_charset("utf8");

// Set timezone internal PHP ke Jakarta agar fungsi date() sinkron dengan dashboard
date_default_timezone_set('Asia/Jakarta');


?>