<?php
session_start();

include("../Database/connect.php");
$id_order = isset($_POST["id_order"]) ? trim($_POST["id_order"]) : "";

if (isset($_POST['input_order_delete'])) {
        if ($id_order === '') {
                echo "<script>alert('Kode order tidak valid'); window.location.href='../order';</script>";
                exit();
        }

        $select_statement = mysqli_prepare($conn, "SELECT 1 FROM tb_list_order WHERE kode_order = ? LIMIT 1");
        mysqli_stmt_bind_param($select_statement, "s", $id_order);
        mysqli_stmt_execute($select_statement);
        mysqli_stmt_store_result($select_statement);

        if (mysqli_stmt_num_rows($select_statement) > 0) {
                mysqli_stmt_close($select_statement);
                echo "<script>alert('Order tidak dapat dihapus karena masih ada item terkait'); window.location.href='../order';</script>";
                exit();
        }
        mysqli_stmt_close($select_statement);

        mysqli_begin_transaction($conn);

        try {
                $delete_payment = mysqli_prepare($conn, "DELETE FROM tb_bayar WHERE id_bayar = ? OR kode_order_bayar = ?");
                mysqli_stmt_bind_param($delete_payment, "ss", $id_order, $id_order);
                mysqli_stmt_execute($delete_payment);
                mysqli_stmt_close($delete_payment);

                $delete_order = mysqli_prepare($conn, "DELETE FROM tb_order WHERE id_order = ?");
                mysqli_stmt_bind_param($delete_order, "s", $id_order);
                mysqli_stmt_execute($delete_order);

                if (mysqli_stmt_affected_rows($delete_order) !== 1) {
                        throw new RuntimeException('Order tidak ditemukan');
                }

                mysqli_stmt_close($delete_order);
                mysqli_commit($conn);
                echo "<script>alert('Order berhasil dihapus'); window.location.href='../order';</script>";
        } catch (Throwable $error) {
                mysqli_rollback($conn);
                error_log('Gagal menghapus order ' . $id_order . ': ' . $error->getMessage());
                echo "<script>alert('Gagal menghapus order'); window.location.href='../order';</script>";
        }
}
