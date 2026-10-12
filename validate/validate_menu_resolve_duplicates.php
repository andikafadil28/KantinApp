<?php
session_start();

include("../Database/connect.php");

if (!isset($_SESSION['level_kantin']) || (int) $_SESSION['level_kantin'] !== 1) {
    echo "<script>alert('Akses ditolak'); window.location.href='../menu';</script>";
    exit();
}

$pending = isset($_SESSION['menu_pending_duplicates']) && is_array($_SESSION['menu_pending_duplicates'])
    ? $_SESSION['menu_pending_duplicates']
    : [];
$action = isset($_POST['action']) ? $_POST['action'] : 'skip';

// Selalu bersihkan pending agar tidak ikut terpakai ulang.
unset($_SESSION['menu_pending_duplicates'], $_SESSION['menu_pending_summary']);

if ($action !== 'force' || empty($pending)) {
    echo "<script>alert('Menu duplikat dilewati'); window.location.href='../menu';</script>";
    exit();
}

$insert_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO tb_menu (nama, foto, keterangan, kategori, nama_toko, harga, pajak, status)
     VALUES (?, '', ?, ?, ?, ?, ?, ?)"
);

if (!$insert_stmt) {
    echo "<script>alert('Gagal menyiapkan query'); window.location.href='../menu';</script>";
    exit();
}

$inserted = 0;
$failed = 0;

foreach ($pending as $row) {
    $nama = isset($row['nama']) ? (string) $row['nama'] : '';
    if ($nama === '') {
        continue;
    }

    $keterangan = isset($row['keterangan']) ? (string) $row['keterangan'] : '';
    $kategori = isset($row['kategori']) ? (string) $row['kategori'] : '';
    $kios = isset($row['kios']) ? (string) $row['kios'] : '';
    $harga = isset($row['harga']) ? (float) $row['harga'] : 0;
    $pajak = isset($row['pajak']) ? (float) $row['pajak'] : 0;
    $status = (isset($row['status']) && (int) $row['status'] === 1) ? 1 : 0;

    mysqli_stmt_bind_param($insert_stmt, "ssssddi", $nama, $keterangan, $kategori, $kios, $harga, $pajak, $status);

    $executed = false;
    try {
        $executed = mysqli_stmt_execute($insert_stmt);
    } catch (\mysqli_sql_exception $e) {
        $executed = false;
    }

    if ($executed) {
        $inserted++;
    } else {
        $failed++;
    }
}

mysqli_stmt_close($insert_stmt);

$msg = "$inserted menu duplikat ditambahkan.";
if ($failed > 0) {
    $msg .= " $failed gagal.";
}

echo "<script>alert(" . json_encode($msg) . "); window.location.href='../menu';</script>";
exit();
