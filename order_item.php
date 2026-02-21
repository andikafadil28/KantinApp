<?php
include "Database/connect.php";

$query = mysqli_query($conn, "SELECT *,
SUM(
    CASE
        WHEN tb_menu.kategori = 3 THEN 0
        ELSE ((harga+pajak)*0.11)*jumlah
    END
) as ppn_pajak,
SUM(
    CASE
        WHEN tb_menu.kategori = 3 THEN harga*jumlah
        ELSE ((harga+pajak)+((harga+pajak)*0.11))*jumlah
    END
) AS harganya,
(
    CASE
        WHEN tb_menu.kategori = 3 THEN harga
        ELSE (harga+pajak)+((harga+pajak)*0.11)
    END
) AS harga_jual,
sum(harga*jumlah) AS harganya_toko
from tb_list_order
LEFT JOIN tb_order ON tb_order.id_order = tb_list_order.kode_order
LEFT JOIN tb_menu ON tb_menu.id = tb_list_order.menu
LEFT JOIN tb_bayar ON tb_bayar.id_bayar = tb_list_order.kode_order
GROUP BY tb_list_order.id_list_order
HAVING tb_list_order.kode_order = $_GET[kode_order]");
$kode = $_GET['kode_order'];
$meja = $_GET['meja'];
$customer = $_GET['pelanggan'];
$toko = $_GET['kios'];
$diskon = $_GET['diskon'] ?? 0;
$waktu_order = $GET['waktu_order'] ?? date('Y-m-d H:i:s');
$result_menu = [];
$result_menu_regular = [];
$result_menu_addon = [];
$result = [];
$result2 = [];
$set_menu = mysqli_query($conn, "SELECT tb_menu.id,tb_menu.nama FROM tb_menu
WHERE tb_menu.nama_toko = '$toko' AND tb_menu.status = 1");
$set_menu_regular = mysqli_query($conn, "SELECT tb_menu.id,tb_menu.nama FROM tb_menu
WHERE tb_menu.nama_toko = '$toko' AND tb_menu.status = 1 AND tb_menu.kategori <> 3");
$set_menu_addon = mysqli_query($conn, "SELECT tb_menu.id,tb_menu.nama FROM tb_menu
WHERE tb_menu.nama_toko = '$toko' AND tb_menu.status = 1 AND tb_menu.kategori = 3");
$query2 = mysqli_query($conn, "select * from tb_kios");

while ($record_menu = mysqli_fetch_array($set_menu)) {
    $result_menu[] = $record_menu;
}
while ($record_menu_regular = mysqli_fetch_array($set_menu_regular)) {
    $result_menu_regular[] = $record_menu_regular;
}
while ($record_menu_addon = mysqli_fetch_array($set_menu_addon)) {
    $result_menu_addon[] = $record_menu_addon;
}

while ($record = mysqli_fetch_array($query)) {
    $result[] = $record;
    // $kode = $record['id_order'];
    // $meja = $record['meja'];
    // $customer = $record['pelanggan'];
    // $toko = $record['nama_toko'];
}

// var_dump($result);
// exit(); 
while ($record2 = mysqli_fetch_array($query2)) {
    $result2[] = $record2;
}


?>

<!-- Conten -->
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-fork-knife"></i>
            Setingan User
        </div>
        <div class="card-body-scrollable">
            <a href="order" class="btn btn-info mb-3">back</a>
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-floating ">
                        <input disabled type="text" class="form-control" id="id_order"
                            value="<?php echo $kode ?>" name="id_order">
                        <label for="floatingInputGambar">Kode Order</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating ">
                        <input disabled type="text" class="form-control" id="meja"
                            value="<?php echo $meja ?>" name="meja">
                        <label for="floatingInputGambar">Nomor Meja</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating ">
                        <input disabled type="text" class="form-control" id="pelanggan"
                            value="<?php echo $customer ?>" name="pelanggan">
                        <label for="floatingInputGambar">Pelanggan</label>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-floating ">
                        <input disabled type="text" class="form-control" id="toko"
                            value="<?php echo $toko ?>" name="toko">
                        <label for="floatingInputGambar">Nama Kios</label>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">

                </div>
                <div class="row mt-3">
                    <?php
                    include "inc/modal/modal_order_item.php";
                    ?>
                    <?php
                    if (empty($result)) {
                    } else {
                    ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Menu</th>
                                        <th scope="col">Harga</th>
                                        <th scope="col">Qty</th>
                                        <th scope="col">Catatan</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    foreach ($result as $row) {
                                    ?>
                                        <tr>
                                            <td>
                                                <?php echo $row['nama'] ?>
                                                <?php if ((int)($row['kategori'] ?? 0) === 3) { ?>
                                                    <span class="badge bg-info ms-1">Addon</span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo number_format($row['harga_jual'], 0, ',', '.') ?></td>
                                            <td><?php echo $row['jumlah'] ?></td>
                                            <td><?php echo $row['catatan_order'] ?></td>
                                            <td><?php echo number_format($row['harganya'], 0, ',', '.') ?></td>
                                            <td>
                                                <div class="d-flex">
                                                    <?php
                                                    if ($_SESSION["level_kantin"] == 1) {

                                                    ?>
                                                        <button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#ModalEdit<?php echo $row['id_list_order'] ?>"> <i class="bi bi-pencil-fill"></i></button>
                                                        <button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#ModalDelete<?php echo $row['id_list_order'] ?>"> <i class="bi bi-trash-fill"></i></button>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary btn-sm me-2 disabled" : "btn btn-warning btn-sm me-2 ";  ?> " data-bs-toggle="modal" data-bs-target="#ModalEdit<?php echo $row['id_list_order'] ?>"> <i class="bi bi-pencil-fill"></i></button>
                                                        <button class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary btn-sm me-2 disabled" : "btn btn-danger btn-sm me-2";  ?> " data-bs-toggle="modal" data-bs-target="#ModalDelete<?php echo $row['id_list_order'] ?>"> <i class="bi bi-trash-fill"></i></button>
                                                    <?php
                                                    }
                                                    ?>


                                                </div>

                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-bold" colspan="4">
                                            Total Harga
                                        </td>
                                        <td class="fw-bold">
                                            <?php
                                            $total = 0;
                                            foreach ($result as $row) {
                                                $total += $row['harganya'];
                                            }
                                            echo number_format($total, 0, ',', '.');
                                            ?>
                                        </td>

                                    </tr>

                                    <tr>
                                        <td colspan="4" class="fw-bold">
                                            Grand Total
                                        </td>
                                        <td class="fw-bold">
                                            <?php
                                            // Ambil nilai diskon nominal dari input user jika ada, jika tidak pakai 0
                                            $diskon_nominal = isset($_POST['diskon_nominal']) ? floatval($_POST['diskon_nominal']) : 0;
                                            if ($diskon_nominal < 0) $diskon_nominal = 0;
                                            if ($diskon_nominal > $total) $diskon_nominal = $total;


                                            $grand_total = $total - $diskon_nominal;


                                            ?>
                                            <form method="post" action="">
                                                <div class="mb-2">
                                                    <label for="diskon_nominal" class="form-label">Diskon (Rp)</label>
                                                    <input type="number" min="0" max="<?php echo $total; ?>" step="1" name="diskon_nominal" id="diskon_nominal" class="form-control form-control-sm d-inline-block" style="width:120px;" value="<?php echo htmlspecialchars($diskon_nominal + $diskon); ?>" onchange="this.form.submit()">
                                                </div>
                                            </form>
                                            <div>Diskon: -<?php echo number_format($diskon_nominal + $diskon, 0, ',', '.'); ?></div>
                                            <!-- <div>Total Harga: <?php echo number_format($grand_total - $diskon, 0, ',', '.'); ?> </div> -->
                                            <?php
                                            $grand_total = $grand_total - $diskon;
                                            // $ppn = $grand_total * 0.11;
                                            // $grand_total += $ppn;
                                            ?>
                                            <!-- <div>PPN 11%: <?php echo number_format($ppn, 0, ',', '.'); ?></div> -->
                                            <div class="fw-bold">Grand Total: <?php echo number_format($grand_total, 0, ',', '.'); ?></div>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    <?php
                    }
                    ?>
                    <div class="row g-3 mb-3">
                        <?php
                        if ($_SESSION["level_kantin"] == 1) {
                        ?>
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">Tambah Item Menu</h6>
                                        <p class="card-text small text-muted mb-3">Item standar dengan perhitungan harga normal.</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahItem"><i class="bi bi-plus-square-dotted"></i> Item</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">Tambah Addon</h6>
                                        <p class="card-text small text-muted mb-3">Addon tidak dikenakan PPN, total berdasarkan harga per barang.</p>
                                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#tambahAddon"><i class="bi bi-plus-square-dotted"></i> Addon</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bayar"><i class="bi bi-cash-coin"></i> Bayar</button>
                                <button class="btn btn-info" onclick="printStruk()"><i class="bi bi-printer"></i> Print Struk</button>
                            </div>
                        <?php
                        } else {
                            // Cek apakah sudah bayar (id_bayar tidak kosong pada salah satu item)
                            $sudah_bayar = false;
                            if (!empty($result)) {
                                foreach ($result as $row) {
                                    if (!empty($row['id_bayar'])) {
                                        $sudah_bayar = true;
                                        break;
                                    }
                                }
                            }
                        ?>
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">Tambah Item Menu</h6>
                                        <p class="card-text small text-muted mb-3">Item standar dengan perhitungan harga normal.</p>
                                        <button class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary disabled" : "btn btn-primary"; ?>" data-bs-toggle="modal" data-bs-target="#tambahItem"><i class="bi bi-plus-square-dotted"></i> Item</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">Tambah Addon</h6>
                                        <p class="card-text small text-muted mb-3">Addon tidak dikenakan PPN, total berdasarkan harga per barang.</p>
                                        <button class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary disabled" : "btn btn-info"; ?>" data-bs-toggle="modal" data-bs-target="#tambahAddon"><i class="bi bi-plus-square-dotted"></i> Addon</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button class="<?php echo (!empty($row['id_bayar'])) ? "btn btn-secondary disabled" : "btn btn-success"; ?>" data-bs-toggle="modal" data-bs-target="#bayar"><i class="bi bi-cash-coin"></i> Bayar</button>
                                <button class="btn btn-info<?php echo $sudah_bayar ? '' : ' disabled'; ?>" onclick="if(<?php echo $sudah_bayar ? 'true' : 'false'; ?>) printStruk()"><i class="bi bi-printer"></i> Print Struk</button>
                            </div>
                        <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="strukContent" style="display: none;">

        <style>
            /* --- CSS STYLING UNTUK STRUK KASIR (60MM) - FONT TEBAL --- */
            #struk_body {
                width: 60mm;
                font-family: 'Courier New', monospace;
                text-align: left;
                font-size: 12px;
                padding: 5px;
                /* PENTING: Membuat semua teks di body menjadi tebal secara default */
                font-weight: bold;
            }

            #struk_body h2 {
                text-align: center;
                margin: 5px 0;
                /* Judul lebih besar dan tebal */
                font-size: 16px;
                font-weight: 900;
                /* Atau bolder */
            }

            #struk_body table {
                width: 100%;
                font-size: 12px;
                text-align: left;
                margin: 5px 0;
                border-collapse: collapse;
            }

            #struk_body table th,
            #struk_body table td {
                border: none;
                padding: 1px 0;
                vertical-align: top;
                font-weight: bold;
            }

            /* Utility classes untuk perataan teks */
            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            /* Garis pemisah */
            .separator {
                border-top: 1px dashed black;
                margin: 3px 0;
            }

            .grand-total-line {
                border-top: 2px solid black;
                padding-top: 5px;
                margin-top: 5px;
            }

            /* Style untuk detail kecil (harga satuan, catatan) - dibuat tidak tebal agar ada kontras */
            .small-detail {
                font-size: 10px;
                font-style: italic;
                font-weight: normal;
                /* Override bold agar tidak terlalu ramai */
            }
        </style>

        <div id="struk_body" class="container">
            <h2 class="text-center">Kantin Sakina</h2>
            <h2 class="text-center">Struk Pembayaran</h2>

            <div class="separator"></div>

            <div>Waktu Order: <?php echo $waktu_order ?></div>
            <div>Kode Order: <?php echo $kode; ?></div>
            <div>Meja: <?php echo $meja; ?> / Pelanggan: <?php echo $customer; ?></div>
            <div>Kios: <?php echo $toko; ?></div>

            <div class="separator"></div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 55%;">Menu</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-right" style="width: 35%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($result as $row) {
                        $harga_unit = number_format($row['harga_jual'], 0, ',', '.');
                        $total_item = number_format($row['harganya'], 0, ',', '.');
                    ?>
                        <tr>
                            <td colspan="1">
                                <?php echo $row['nama']; ?>
                                <?php if ((int)($row['kategori'] ?? 0) === 3) {
                                    echo " (Addon)";
                                } ?>
                            </td>
                            <td class="text-center">
                                <?php echo $row['jumlah']; ?>
                            </td>
                            <td class="text-right">
                                <?php echo $total_item; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="small-detail">
                                @ <?php echo $harga_unit; ?>
                            </td>
                            <td colspan="2" class="small-detail">
                                <?php echo (!empty($row['catatan_order']) ? 'Catatan: ' . $row['catatan_order'] : ''); ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>

            <div class="separator"></div>

            <div class="text-right">
                <div>Diskon: -<?php echo number_format($diskon_nominal + $diskon, 0, ',', '.'); ?></div>
                <div>Total: <?php echo number_format($total - $diskon, 0, ',', '.'); ?></div>
                <!-- <div>PPN : <?php echo number_format($ppn, 0, ',', '.'); ?> </div> -->

                <h3 class="grand-total-line">
                    Grand Total: <?php echo number_format($grand_total, 0, ',', '.'); ?>
                </h3>

            </div>
            <div class="text-center small-detail" style="margin-top: 10px;font-weight: bold;">
                *Harga Sudah Termasuk Pajak
            </div>
            <div class="text-center small-detail" style="margin-top: 10px;font-weight: bold;">
                TERIMA KASIH ATAS KUNJUNGAN ANDA!
            </div>

        </div>
    </div>



    <script>
        function printStruk() {
            var strukContent = document.getElementById("strukContent").innerHTML;
            var printFrame = document.createElement("iframe");
            printFrame.style.display = "none";
            document.body.appendChild(printFrame);
            printFrame.contentDocument.write(strukContent);
            printFrame.contentWindow.print();
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#menu-pilihan').select2({
                // Opsi untuk placeholder, akan muncul di kotak pencarian
                placeholder: 'Ketik nama menu...',
                allowClear: true // Memungkinkan pengguna untuk menghapus pilihan
            });
        });
    </script>


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
    <style>
        .logo-struk {
            width: 5px;
            height: auto;
        }
    </style>

    <style>
        /* Include the CSS here or link to an external stylesheet */
        .card {
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 97%;
            /* Example width for the card */
            margin: 20px;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            background-color: #f0f0f0;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }

        .card-body-scrollable {
            overflow-x: auto;
            /* Adds horizontal scrollbar when content overflows */
            padding: 15px;
            /* white-space: nowrap; /* Uncomment if you want text to stay on one line */
        }

        .long-content {
            min-width: 800px;
            /* Ensure content is wide enough to trigger scroll */
            /* Adjust this value based on your content's natural width */
        }
    </style>
