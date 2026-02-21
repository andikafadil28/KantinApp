<?php
session_start();



include("../Database/connect.php");
$kodeorder = (isset($_POST["kode_order"])) ? htmlentities($_POST["kode_order"]) : "";
$meja = (isset($_POST["meja"])) ? htmlentities($_POST["meja"]) : "";
$pelanggan = (isset($_POST["pelanggan"])) ? htmlentities($_POST["pelanggan"]) : "";
$catatan_order = (isset($_POST["catatan_order"])) ? htmlentities($_POST["catatan_order"]) : "";
$jumlah = (isset($_POST["jumlah"])) ? htmlentities($_POST["jumlah"]) : "";
$menu = (isset($_POST["menu"])) ? htmlentities($_POST["menu"]) : "";
$toko = (isset($_POST["kios"])) ? htmlentities($_POST["kios"]) : "";
$item_type = (isset($_POST["item_type"])) ? htmlentities($_POST["item_type"]) : "regular";
$item_type = ($item_type === "addon") ? "addon" : "regular";

function redirect_orderitem($kodeorder, $meja, $pelanggan, $toko)
{
        $url = "../?x=orderitem"
                . "&kode_order=" . rawurlencode($kodeorder)
                . "&meja=" . rawurlencode($meja)
                . "&pelanggan=" . rawurlencode($pelanggan)
                . "&kios=" . rawurlencode($toko);
        header("Location: " . $url);
        exit();
}


if (isset($_POST['input_order_item_proses'])) {
        if (empty($menu) || (int)$jumlah <= 0) {
                $_SESSION['flash_message'] = "Menu dan jumlah wajib diisi";
                redirect_orderitem($kodeorder, $meja, $pelanggan, $toko);
        }

        $cek_menu_query = mysqli_query($conn, "SELECT tb_menu.id
                FROM tb_menu
                WHERE tb_menu.id = '$menu'
                AND tb_menu.nama_toko = '$toko'
                AND tb_menu.status = 1
                AND (
                        ('$item_type' = 'addon' AND tb_menu.kategori = 3)
                        OR
                        ('$item_type' <> 'addon' AND tb_menu.kategori <> 3)
                )");
        if (mysqli_num_rows($cek_menu_query) == 0) {
                $_SESSION['flash_message'] = "Menu tidak valid untuk tipe item yang dipilih";
                redirect_orderitem($kodeorder, $meja, $pelanggan, $toko);
        }

        $select_query = mysqli_query($conn, "SELECT * FROM tb_list_order WHERE kode_order = '$kodeorder' AND menu = '$menu'");
        if (mysqli_num_rows($select_query) > 0) {
                $_SESSION['flash_message'] = "Item sudah terdaftar dalam order ini";
                redirect_orderitem($kodeorder, $meja, $pelanggan, $toko);
        } else {
                $query = mysqli_query($conn, "INSERT INTO tb_list_order (kode_order, menu, jumlah, catatan_order,status) VALUES ('$kodeorder', '$menu', '$jumlah', '$catatan_order','0')");
                if ($query) {
                        redirect_orderitem($kodeorder, $meja, $pelanggan, $toko);
                } else {
                        echo "<script>alert('Gagal menambahkan item'); window.location.href='../order';</script>";
                }
        }

}

