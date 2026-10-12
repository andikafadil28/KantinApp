<?php
session_start();

include("../Database/connect.php");
$nama_menu = (isset($_POST["nama_menu"])) ? htmlentities($_POST["nama_menu"]) : "";
$keterangan = (isset($_POST["keterangan"])) ? htmlentities($_POST["keterangan"]) : "";
$kategori_menu = (isset($_POST["kategori_menu"])) ? htmlentities($_POST["kategori_menu"]) : "";
$harga = (isset($_POST["harga"])) ? htmlentities($_POST["harga"]) : "";
$stok = (isset($_POST["stok"])) ? htmlentities($_POST["stok"]) : "";
$kios = (isset($_POST["kios"])) ? htmlentities($_POST["kios"]) : "";
$pajak = (isset($_POST["pajak"])) ? htmlentities($_POST["pajak"]) : "";
$status = isset($_POST["status_aktif"]) ? 1 : 0;


$kode_rand = rand(1000, 9999) . "-";
$target_dir = "../assets/img/" . $kode_rand;
$foto_name = isset($_FILES["foto"]["name"]) ? $_FILES["foto"]["name"] : "";
$target_file = $target_dir . basename($foto_name);
$imageType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


if (isset($_POST['input_menu_proses'])) {
    $hasPhoto = isset($_FILES["foto"]["tmp_name"]) && is_uploaded_file($_FILES["foto"]["tmp_name"]);
    $statusUpload = 1;
    $namaFileFoto = "";

    if ($hasPhoto) {
        $cek = getimagesize($_FILES["foto"]["tmp_name"]);
        if ($cek === false) {
            echo "<script>alert('File yang diupload bukan gambar'); window.location.href='../menu';</script>";
            $statusUpload = 0;
        } else {
            if (file_exists($target_file)) {
                echo "<script>alert('File sudah ada'); window.location.href='../menu';</script>";
                $statusUpload = 0;
            } elseif ($_FILES["foto"]["size"] > 500000) {
                echo "<script>alert('File terlalu besar'); window.location.href='../menu';</script>";
                $statusUpload = 0;
            } elseif ($imageType != "jpg" && $imageType != "png" && $imageType != "jpeg" && $imageType != "gif") {
                echo "<script>alert('Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan'); window.location.href='../menu';</script>";
                $statusUpload = 0;
            }
        }
    }

    if ($statusUpload == 0) {
        exit();
    }

    $select_query = mysqli_query($conn, "SELECT * FROM tb_menu WHERE nama = '$nama_menu' AND nama_toko = '$kios'");
    if (mysqli_num_rows($select_query) > 0) {
        echo "<script>alert('Menu sudah terdaftar di kios tersebut'); window.location.href='../menu';</script>";
        exit();
    }

    if ($hasPhoto) {
        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            echo "<script>alert('Gagal mengupload gambar'); window.location.href='../menu';</script>";
            exit();
        }
        $namaFileFoto = $kode_rand . $_FILES['foto']['name'];
    }

    $query = mysqli_query($conn, "INSERT INTO tb_menu (nama, foto, keterangan , kategori , nama_toko , harga, pajak, status) 
            VALUES ('$nama_menu', '$namaFileFoto', '$keterangan', '$kategori_menu', '$kios', '$harga', '$pajak', '$status')");
    if ($query) {
        echo "<script>alert('Menu berhasil ditambahkan'); window.location.href='../menu';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan menu'); window.location.href='../menu';</script>";
    }

    exit();


    $select_query = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    if (mysqli_num_rows($select_query) > 0) {
        echo "<script>alert('Username sudah terdaftar'); window.location.href='../user';</script>";
        exit();
    } else {
        $query = mysqli_query($conn, "INSERT INTO user (username, password, level, kios) VALUES ('$username', '$password_hash', '$level', '$kios')");
        if ($query) {
            echo "<script>alert('User berhasil ditambahkan'); window.location.href='../user';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan user'); window.location.href='../user';</script>";
        }
    }
}
