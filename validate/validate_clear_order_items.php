<?php
session_start();

include("../Database/connect.php");

if (!isset($_POST['clear_order_items'])) {
        header('Location: ../order');
        exit();
}

if (empty($_SESSION['username_kantin'])) {
        header('Location: ../login');
        exit();
}

$id_order = isset($_POST['id_order']) ? trim($_POST['id_order']) : '';

if ($id_order === '') {
        echo "<script>alert('Kode order tidak valid'); window.location.href='../order';</script>";
        exit();
}

$order_statement = mysqli_prepare(
        $conn,
        "SELECT tb_order.nama_kios, EXISTS(SELECT 1 FROM tb_bayar WHERE id_bayar = tb_order.id_order OR kode_order_bayar = tb_order.id_order) AS is_paid
         FROM tb_order
         WHERE tb_order.id_order = ?"
);
mysqli_stmt_bind_param($order_statement, "s", $id_order);
mysqli_stmt_execute($order_statement);
$order_result = mysqli_stmt_get_result($order_statement);
$order = mysqli_fetch_assoc($order_result);
mysqli_stmt_close($order_statement);

if (!$order) {
        echo "<script>alert('Order tidak ditemukan'); window.location.href='../order';</script>";
        exit();
}

$is_admin = (int) ($_SESSION['level_kantin'] ?? 0) === 1;
$is_own_kiosk = ($order['nama_kios'] ?? '') === ($_SESSION['nama_toko_kantin'] ?? '');

if (!$is_admin && !$is_own_kiosk) {
        echo "<script>alert('Anda tidak memiliki akses ke order ini'); window.location.href='../order';</script>";
        exit();
}

if (!$is_admin && (int) $order['is_paid'] === 1) {
        echo "<script>alert('Order yang sudah dibayar hanya dapat diubah oleh admin'); window.location.href='../order';</script>";
        exit();
}

$delete_statement = mysqli_prepare($conn, "DELETE FROM tb_list_order WHERE kode_order = ?");
mysqli_stmt_bind_param($delete_statement, "s", $id_order);
mysqli_stmt_execute($delete_statement);
$deleted_items = mysqli_stmt_affected_rows($delete_statement);
mysqli_stmt_close($delete_statement);

if ($deleted_items > 0) {
        echo "<script>alert('Semua item order berhasil dihapus'); window.location.href='../order';</script>";
} else {
        echo "<script>alert('List item order sudah kosong'); window.location.href='../order';</script>";
}
