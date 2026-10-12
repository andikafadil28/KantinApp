<?php
session_start();

include("../Database/connect.php");

if (!isset($_SESSION['level_kantin']) || (int) $_SESSION['level_kantin'] !== 1) {
    echo "<script>alert('Akses ditolak'); window.location.href='../menu';</script>";
    exit();
}

if (!isset($_POST['input_menu_bulk_proses'])) {
    header('Location: ../menu');
    exit();
}

function bulk_post_array($key)
{
    return (isset($_POST[$key]) && is_array($_POST[$key])) ? $_POST[$key] : [];
}

function dedup_key($nama, $kios)
{
    return strtolower(trim($nama)) . "\0" . strtolower(trim($kios));
}

$nama_list = bulk_post_array('nama_menu');
$keterangan_list = bulk_post_array('keterangan');
$kategori_list = bulk_post_array('kategori_menu');
$harga_list = bulk_post_array('harga');
$pajak_list = bulk_post_array('pajak');
$kios_list = bulk_post_array('kios');
$status_list = bulk_post_array('status_aktif');

// Preload pasangan nama+kios yang sudah ada sekali (hindari N+1 query saat cek duplikat).
$existing = [];
$res = mysqli_query($conn, "SELECT nama, nama_toko FROM tb_menu");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $existing[dedup_key($row['nama'], $row['nama_toko'])] = true;
    }
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
$skipped = 0;
$errors = [];
$duplicates = [];

foreach ($nama_list as $i => $nama_raw) {
    $nama = trim((string) $nama_raw);
    if ($nama === '') {
        continue; // baris kosong diabaikan
    }

    $keterangan = isset($keterangan_list[$i]) ? trim((string) $keterangan_list[$i]) : '';
    $kategori = isset($kategori_list[$i]) ? trim((string) $kategori_list[$i]) : '';
    $harga = isset($harga_list[$i]) ? trim((string) $harga_list[$i]) : '';
    $pajak = isset($pajak_list[$i]) ? trim((string) $pajak_list[$i]) : '';
    $kios = isset($kios_list[$i]) ? trim((string) $kios_list[$i]) : '';
    $status = (isset($status_list[$i]) && (string) $status_list[$i] === '1') ? 1 : 0;

    if (
        $kategori === '' || $kios === '' ||
        !is_numeric($harga) || !is_numeric($pajak)
    ) {
        $errors[] = $nama;
        continue;
    }

    $harga_val = (float) $harga;
    $pajak_val = (float) $pajak;

    $key = dedup_key($nama, $kios);
    if (isset($existing[$key])) {
        $skipped++;
        $duplicates[] = [
            'nama' => $nama,
            'keterangan' => $keterangan,
            'kategori' => $kategori,
            'kios' => $kios,
            'harga' => $harga_val,
            'pajak' => $pajak_val,
            'status' => $status,
        ];
        continue;
    }

    mysqli_stmt_bind_param(
        $insert_stmt,
        "ssssddi",
        $nama,
        $keterangan,
        $kategori,
        $kios,
        $harga_val,
        $pajak_val,
        $status
    );

    if (mysqli_stmt_execute($insert_stmt)) {
        $inserted++;
        $existing[$key] = true; // cegah duplikat antar-baris dalam batch yang sama
    } else {
        $errors[] = $nama;
    }
}

mysqli_stmt_close($insert_stmt);

// Duplikat dikirim ke halaman konfirmasi, bukan langsung dibuang.
if (!empty($duplicates)) {
    $_SESSION['menu_pending_duplicates'] = $duplicates;
    $_SESSION['menu_pending_summary'] = [
        'inserted' => $inserted,
        'skipped' => $skipped,
        'errors' => $errors,
        'source' => 'bulk',
    ];
    echo "<script>window.location.href='../menuduplikat';</script>";
    exit();
}

$msg = "$inserted menu ditambahkan.";
if (count($errors) > 0) {
    $preview = implode(', ', array_slice($errors, 0, 5));
    if (count($errors) > 5) {
        $preview .= ', ...';
    }
    $msg .= " " . count($errors) . " gagal (data tidak lengkap): $preview";
}

echo "<script>alert(" . json_encode($msg) . "); window.location.href='../menu';</script>";
exit();
