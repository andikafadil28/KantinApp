<?php
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);
$dbName = getenv('DB_NAME') ?: 'sakinakantin';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';

$conn = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
if (!$conn) {
      error_log('Koneksi database gagal: ' . mysqli_connect_error());
      http_response_code(503);
      exit('Layanan database tidak tersedia');
}

mysqli_set_charset($conn, 'utf8mb4');
?>
