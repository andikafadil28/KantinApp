<?php
session_start();

include("../Database/connect.php");

if (!isset($_SESSION['level_kantin']) || (int) $_SESSION['level_kantin'] !== 1) {
    echo "<script>alert('Akses ditolak'); window.location.href='../menu';</script>";
    exit();
}

$raw_ids = (isset($_POST['ids']) && is_array($_POST['ids'])) ? $_POST['ids'] : [];
$ids = array_values(array_filter(array_map(function ($value) {
    return ctype_digit((string) $value) ? (int) $value : null;
}, $raw_ids)));

if (empty($ids)) {
    echo "<script>alert('Tidak ada menu yang dipilih'); window.location.href='../menu';</script>";
    exit();
}

$select_stmt = mysqli_prepare($conn, "SELECT foto FROM tb_menu WHERE id = ?");
$delete_stmt = mysqli_prepare($conn, "DELETE FROM tb_menu WHERE id = ?");

if (!$select_stmt || !$delete_stmt) {
    echo "<script>alert('Gagal menyiapkan query'); window.location.href='../menu';</script>";
    exit();
}

$deleted = 0;
$not_found = 0;
$used_in_order = 0;
$failed = 0;

foreach ($ids as $id) {
    $foto = '';
    $exists = false;

    mysqli_stmt_bind_param($select_stmt, "i", $id);
    try {
        if (mysqli_stmt_execute($select_stmt)) {
            $result = mysqli_stmt_get_result($select_stmt);
            if ($result && ($row = mysqli_fetch_assoc($result))) {
                $foto = (string) $row['foto'];
                $exists = true;
            }
        }
    } catch (\mysqli_sql_exception $e) {
        $exists = false;
    }

    if (!$exists) {
        $not_found++;
        continue;
    }

    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    $ok = false;
    try {
        $ok = mysqli_stmt_execute($delete_stmt);
    } catch (\mysqli_sql_exception $e) {
        $ok = false;
    }

    if ($ok) {
        $deleted++;
        // Hapus file foto hanya jika ada dan namanya valid.
        $safe_foto = basename($foto);
        $path_foto = "../assets/img/" . $safe_foto;
        if ($safe_foto !== '' && file_exists($path_foto)) {
            @unlink($path_foto);
        }
    } else {
        $db_error = mysqli_stmt_error($delete_stmt);
        if (stripos($db_error, 'foreign key') !== false) {
            $used_in_order++;
        } else {
            $failed++;
        }
    }
}

mysqli_stmt_close($select_stmt);
mysqli_stmt_close($delete_stmt);

$msg = "$deleted menu dihapus.";
if ($used_in_order > 0) {
    $msg .= " $used_in_order tidak bisa dihapus (masih dipakai di order).";
}
if ($failed > 0) {
    $msg .= " $failed gagal.";
}
if ($not_found > 0) {
    $msg .= " $not_found tidak ditemukan.";
}

echo "<script>alert(" . json_encode($msg) . "); window.location.href='../menu';</script>";
exit();
