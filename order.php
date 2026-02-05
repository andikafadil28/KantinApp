<?php

include "Database/connect.php";
date_default_timezone_set("Asia/Jakarta");
$where_clause = "";
$kios_filter = "";
// $sel_kategori = mysqli_query($conn, "SELECT id_kategor i,kategori_menu FROM tb_kategori_menu");
$query2 = mysqli_query($conn, "select * from tb_kios");


// var_dump($result);
// exit();
while ($record2 = mysqli_fetch_array($query2)) {
    $result2[] = $record2;
}


?>

<div class="container-fluid py-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-dark text-white p-3">
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-gear-fill me-2"></i> Manajemen Transaksi Order
            </h4>
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <form method="POST">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="kios_filter" class="form-label fw-semibold">Nama Kios</label>
                            <select class="form-select" id="kios_filter" name="kios_filter">
                                <option selected hidden>Pilih</option>
                                <option value="all">All</option>
                                <?php
                                foreach ($result2 as $row2) {
                                    $selected = (isset($_POST['kios_filter']) && $_POST['kios_filter'] == $row2['nama']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row2['nama'] ?>" <?php echo $selected; ?>><?php echo $row2['nama'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2 d-grid">
                            <button type="submit" class="btn btn-primary" name="filter" value="filter">
                                <i class="bi bi-funnel-fill me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="row">
                <?php
                // Logika Query SQL (TIDAK DIUBAH SAMA SEKALI)
                if (isset($_POST['filter']) && isset($_POST['kios_filter']) && $_POST['kios_filter'] === 'all') {
                    // Filter untuk SEMUA kios
                    $query_string = "SELECT *, SUM((harga+pajak)*jumlah) AS harganya from tb_order
                                             LEFT JOIN user ON user.id = tb_order.kasir
                                             LEFT JOIN tb_list_order ON tb_list_order.kode_order = tb_order.id_order
                                             LEFT JOIN tb_menu ON tb_menu.id = tb_list_order.menu
                                             LEFT JOIN tb_bayar ON tb_bayar.id_bayar = tb_list_order.kode_order
                                             GROUP BY tb_order.id_order ORDER BY tb_order.waktu_order DESC
                                             LIMIT 250";
                } else if (isset($_POST['filter']) && isset($_POST['kios_filter'])) {
                    // Filter untuk kios spesifik
                    $kios_filter = mysqli_real_escape_string($conn, $_POST['kios_filter']);
                    $query_string = "SELECT *, SUM((harga+pajak)*jumlah) AS harganya from tb_order
                                             LEFT JOIN user ON user.id = tb_order.kasir
                                             LEFT JOIN tb_list_order ON tb_list_order.kode_order = tb_order.id_order
                                             LEFT JOIN tb_menu ON tb_menu.id = tb_list_order.menu
                                             LEFT JOIN tb_bayar ON tb_bayar.id_bayar = tb_list_order.kode_order
                                             WHERE tb_order.nama_kios = '$kios_filter'
                                             GROUP BY tb_order.id_order ORDER BY tb_order.waktu_order DESC
                                             LIMIT 250";
                } else if ($_SESSION["level_kantin"] == 3) {
                    $kios_filter = $_SESSION['nama_toko_kantin'];
                    $query_string = "SELECT *, SUM((harga+pajak)*jumlah) AS harganya from tb_order
                                             LEFT JOIN user ON user.id = tb_order.kasir
                                             LEFT JOIN tb_list_order ON tb_list_order.kode_order = tb_order.id_order
                                             LEFT JOIN tb_menu ON tb_menu.id = tb_list_order.menu
                                             LEFT JOIN tb_bayar ON tb_bayar.id_bayar = tb_list_order.kode_order
                                             WHERE tb_order.nama_kios = '$kios_filter'
                                             GROUP BY tb_order.id_order ORDER BY tb_order.waktu_order DESC
                                             LIMIT 250";

                } else {
                    // Query default (tampilkan semua data tanpa filter)
                    $query_string = "SELECT *, SUM((harga+pajak)*jumlah) AS harganya from tb_order
                                             LEFT JOIN user ON user.id = tb_order.kasir
                                             LEFT JOIN tb_list_order ON tb_list_order.kode_order = tb_order.id_order
                                             LEFT JOIN tb_menu ON tb_menu.id = tb_list_order.menu
                                             LEFT JOIN tb_bayar ON tb_bayar.id_bayar = tb_list_order.kode_order
                                             GROUP BY tb_order.id_order ORDER BY tb_order.waktu_order DESC
                                             LIMIT 250";
                }

                // Eksekusi Query
                $query = mysqli_query($conn, $query_string);
                if (!$query) {
                    die("Query Error: " . mysqli_error($conn));
                }

                $result = [];
                while ($record = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                    $result[] = $record;
                }
                ?>
                <div class="col d-flex justify-content-end mb-3">
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#ModalTambah">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Order
                    </button>
                </div>
                <?php
                include "inc/modal/modal_order.php"
                ?>
                <?php
                if (empty($result)) {
                    // Notifikasi data kosong yang lebih menarik
                    echo '<div class="alert alert-warning text-center" role="alert"><i class="bi bi-info-circle me-2"></i> **Tidak ada data order** yang ditemukan.</div>';
                } else {
                ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered caption-top align-middle" id="table_order">
                            <caption class="fw-bold">Daftar Transaksi Order</caption>
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col">Kode Order</th>
                                    <th scope="col">Pelanggan</th>
                                    <th scope="col">Meja</th>
                                    <th scope="col" class="text-end">Diskon</th>
                                    <th scope="col" class="text-end">Total Bayar</th>
                                    <th scope="col">Kasir</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col">Waktu Order</th>
                                    <th scope="col">Nama Toko</th>
                                    <th scope="col" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $id_nomor = 1;
                                foreach ($result as $row) {
                                    $total_bayar_akhir = $row['harganya'] - $row['diskon'] + $row['ppn'];
                                    $is_paid = !empty($row['id_bayar']);
                                ?>
                                    <tr>
                                        <th scope="row" class="text-center"><?php echo $id_nomor++ ?></th>
                                        <td class="fw-bold text-primary"><?php echo $row['id_order'] ?></td>
                                        <td><?php echo $row['pelanggan'] ?></td>
                                        <td><?php echo $row['meja'] ?></td>
                                        <td class="text-end"><?php echo number_format($row['diskon'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="text-end fw-bold text-success"><?php echo number_format($total_bayar_akhir, 0, ',', '.') ?></td>
                                        <td><?php echo $row['username'] ?></td>
                                        <td class="text-center">
                                            <?php echo $is_paid ? "<span class='badge bg-success'><i class='bi bi-check-circle-fill me-1'></i> Dibayar</span>" : "<span class='badge bg-danger'><i class='bi bi-x-circle-fill me-1'></i> Belum Dibayar</span>"; ?>
                                        </td>
                                        <td><?php echo date('d-M-Y H:i', strtotime($row['waktu_order'])) ?></td>
                                        <td><?php echo $row['nama_kios'] ?></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                
                                                <?php
                                                if ($_SESSION["level_kantin"] == 1) {

                                                ?>
                                                    <a class="btn btn-info btn-sm me-2"
                                                        href="./?x=orderitem&kode_order=<?php echo $row['id_order'] . "&meja=" . $row['meja'] . "&pelanggan=" . $row['pelanggan'] . "&kios=" . $row['nama_kios']; ?>&diskon=<?php echo (empty($row['diskon'])) ? 0 : $row['diskon']; ?>"><i
                                                            class="bi bi-eye-fill"></i></a>
                                                    <button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal"
                                                        data-bs-target="#ModalEdit<?php echo $row['id_order'] ?>"> <i
                                                            class="bi bi-pencil-fill"></i></button>
                                                    <button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal"
                                                        data-bs-target="#ModalDelete<?php echo $row['id_order'] ?>"> <i
                                                            class="bi bi-trash-fill"></i></button>
                                                <?php
                                                } else {
                                                ?>
                                                    <a class="btn btn-info btn-sm me-2"
                                                        href="./?x=orderitem&kode_order=<?php echo $row['id_order'] . "&meja=" . $row['meja'] . "&pelanggan=" . $row['pelanggan'] . "&kios=" . $row['nama_kios']; ?>&diskon=<?php echo (empty($row['diskon'])) ? 0 : $row['diskon']; ?>"><i
                                                            class="bi bi-eye-fill"></i></a>
                                                    <button
                                                        class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary btn-sm me-2 disabled" : "btn btn-warning btn-sm me-2 "; ?> "
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#ModalEdit<?php echo $row['id_order'] ?>"> <i
                                                            class="bi bi-pencil-fill"></i></button>
                                                    <button
                                                        class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary btn-sm me-2 disabled" : "btn btn-danger btn-sm me-2"; ?> "
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#ModalDelete<?php echo $row['id_order'] ?>"> <i
                                                            class="bi bi-trash-fill"></i></button>
                                                <?php
                                                }
                                                ?>
                                                <!-- <button class="btn btn-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#ModalView<?php echo $row['id_order'] ?>"> <i class="bi bi-eye-fill"></i></button> -->

                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
<script>
    // Pastikan DataTables sudah dimuat
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof DataTable !== 'undefined') {
            let table = new DataTable('#table_order', {
                responsive: true,
                language: {
                    // Opsional: ganti ke URL DataTables bahasa Indonesia jika tersedia
                    // url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' 
                }
            });
        }
    });
</script>
<style>
    /* Styling Kustom Minimal */
    .card-body-scrollable {
        /* Dihapus karena digantikan oleh .table-responsive */
        padding: 15px; 
    }

    .card {
        /* Menghapus CSS kustom lama yang bertabrakan dengan Bootstrap */
        width: 100%;
        margin: 0;
    }

    /* Memastikan tombol aksi berjarak */
    .d-flex.justify-content-center > * {
        margin-right: 5px; 
    }
</style>