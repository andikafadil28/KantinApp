<style>
    #table_laporan {
        width: 100% !important;
        table-layout: fixed;
        font-size: 12px;
    }

    #table_laporan th,
    #table_laporan td {
        white-space: normal !important;
        word-wrap: break-word;
        overflow-wrap: break-word;
        padding: 6px;
        vertical-align: middle;
    }

    .dataTables_wrapper {
        overflow-x: hidden !important;
    }

    .table-responsive {
        overflow-x: hidden !important;
    }
</style>


<?php
include "Database/connect.php";
date_default_timezone_set("Asia/Jakarta");

// Inisialisasi variabel filter
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$kios_filter = isset($_POST['kios_filter']) ? $_POST['kios_filter'] : '';
$where_clause = "";
$query_string = "";

// Ambil data kios untuk dropdown
$query2 = mysqli_query($conn, "SELECT nama FROM tb_kios ORDER BY nama ASC");
$result2 = [];
while ($record2 = mysqli_fetch_array($query2)) {
    $result2[] = $record2;
}

// Logika penentuan query string utama berdasarkan filter
if (isset($_POST['filter'])) {
    // Sanitasi input
    $start_date_esc = mysqli_real_escape_string($conn, $start_date);
    $end_date_esc = mysqli_real_escape_string($conn, $end_date);
    $kios_filter_esc = mysqli_real_escape_string($conn, $kios_filter);

    $where_parts = [];

    // 1. Filter Kios
    if (!empty($kios_filter_esc) && $kios_filter_esc != 'all') {
        $where_parts[] = "tb_order.nama_kios = '$kios_filter_esc'";
    }

    // 2. Filter Tanggal
    if (!empty($start_date_esc) && !empty($end_date_esc)) {
        $start_date_with_time = $start_date_esc . " 00:00:00";
        $end_date_with_time = $end_date_esc . " 23:59:59";
        $where_parts[] = "tb_order.waktu_order BETWEEN '$start_date_with_time' AND '$end_date_with_time'";
    } else if (!empty($start_date_esc)) {
        $start_date_with_time = $start_date_esc . " 00:00:00";
        $where_parts[] = "tb_order.waktu_order >= '$start_date_with_time'";
    } else if (!empty($end_date_esc)) {
        $end_date_with_time = $end_date_esc . " 23:59:59";
        $where_parts[] = "tb_order.waktu_order <= '$end_date_with_time'";
    }

    if (!empty($where_parts)) {
        $where_clause = " WHERE " . implode(" AND ", $where_parts);
    }

    // Tentukan query string dengan klausa WHERE yang baru dibuat
    include "Database/Query/Rekap_keuangan_detail_where.php";
} else {
    // Query default (Semua data, disarankan ditambahi limit tanggal jika data sangat banyak)
    include "Database/Query/Rekap_keuangan_detail_query.php";
}

// Jalankan query utama
$query = mysqli_query($conn, $query_string);
if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

$result = [];
while ($record = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $result[] = $record;
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-fork-knife"></i>
            Laporan
        </div>
        <div class="card-body">
            <div>
                <form method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="kios_filter" class="form-label">Nama Kios</label>
                            <select class="form-select" aria-label="Default select example" name="kios_filter">
                                <option value="" <?php echo empty($kios_filter) ? 'selected' : ''; ?> hidden>Pilih Kios</option>
                                <?php foreach ($result2 as $row2) { ?>
                                    <option value="<?php echo htmlspecialchars($row2['nama']) ?>" <?php echo $kios_filter == $row2['nama'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row2['nama']) ?></option>
                                <?php } ?>
                                <option value="all" <?php echo $kios_filter == 'all' ? 'selected' : ''; ?>>Semua Kios</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary" name="filter" value="filter">Filter</button>
                        </div>
                        <div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col">

                    <?php
                    // Tampilkan informasi filter yang sedang diterapkan
                    $info_filter = "Menampilkan data laporan";
                    $kios_display = "Semua Kios";

                    if (!empty($kios_filter) && $kios_filter != 'all') {
                        $kios_display = "**" . htmlspecialchars($kios_filter) . "**";
                    }
                    $info_filter .= " untuk kios $kios_display";

                    $tanggal_info = [];
                    if (!empty($start_date)) {
                        $tanggal_info[] = "dari **" . htmlspecialchars($start_date) . "**";
                    }
                    if (!empty($end_date)) {
                        $tanggal_info[] = "sampai **" . htmlspecialchars($end_date) . "**";
                    }

                    if (!empty($tanggal_info)) {
                        $info_filter .= " " . implode(" ", $tanggal_info);
                    } else if (isset($_POST['filter'])) {
                        $info_filter .= " untuk **Semua Waktu**";
                    } else {
                        $info_filter = "Menampilkan data laporan **Semua Waktu dan Semua Kios** (default).";
                    }

                    echo "<p class='alert alert-info'>$info_filter</p>";
                    ?>

                    <div>
                        <table class="table table-hover table-bordered w-100" id="table_laporan">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Waktu Order</th>
                                    <th>Nama Menu</th>
                                    <th>Jumlah</th>
                                    <th>Nama Toko</th>
                                    <th>Harga Jual</th>
                                    <th>PPN</th>
                                    <th>Harga Pembeli</th>
                                    <th>Total Menu</th>
                                    <th>Total PPN</th>
                                    <th>Total Pembeli</th>
                                    <th>Untung Toko</th>
                                    <th>Untung RS</th>
                                    <th>Untung RS + Pajak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $id_nomor = 1;
                                foreach ($result as $row) {
                                ?>
                                    <tr>
                                        <td><?php echo $id_nomor++ ?></td>
                                        <td><?php echo htmlspecialchars($row['Waktu_Order'] ?? '-') ?></td>
                                        <td><?php echo htmlspecialchars($row['Nama_Menu'] ?? '-') ?></td>
                                        <td><?php echo number_format($row['Jumlah_Terjual'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo htmlspecialchars($row['Nama_Toko'] ?? '-') ?></td>
                                        <td><?php echo number_format($row['Harga_Jual_Per_Menu'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Harga_PPN'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Harga_Pembeli_Per_Menu'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Harga_Total_Per_Menu'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Harga_Total_PPN'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Harga_Pembeli_Total'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Keuntungan_Toko'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Keuntungan_RS'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?php echo number_format($row['Keuntungan_RS_Pajak'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <!-- <table class="table table-striped table-bordered w-50">
                        <thead>
                            <tr class="table-primary">
                                <th colspan="2" class="text-center">REKAPITULASI TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_toko = 0;
                            $total_rs = 0;
                            $total_diskon = 0;

                            foreach ($result as $row) {
                                $total_toko += $row['nominal_toko'] ?? 0;
                                $total_rs += $row['nominal_rs'] ?? 0;
                                $total_diskon += $row['diskon'] ?? 0;
                            }
                            $grand_total = $total_toko + $total_rs + $total_diskon;
                            ?>
                            <tr>
                                <td class="fw-bold w-75">Total Pendapatan Toko</td>
                                <td class="fw-bold w-25 text-end">Rp <?php echo number_format($total_toko, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total Pendapatan Sakina Food Court</td>
                                <td class="fw-bold text-end">Rp <?php echo number_format($total_rs, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total Diskon</td>
                                <td class="fw-bold text-end">Rp <?php echo number_format($total_diskon, 0, ',', '.') ?></td>
                            </tr>
                            <tr class="table-success">
                                <td class="fw-bolder">GRAND TOTAL KOTOR (Toko + Food Court + Diskon)</td>
                                <td class="fw-bolder text-end">Rp <?php echo number_format($grand_total, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table> -->
                    <div class="mb-3">
                        <form method="POST" action="excel_export/export_excel_keuangan.php" target="_blank">
                            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            <input type="hidden" name="kios_filter" value="<?php echo htmlspecialchars($kios_filter); ?>">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-file-earmark-excel"></i> Cetak All Data Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof DataTable !== 'undefined') {
                new DataTable('#table_laporan', {
                    scrollX: false,
                    autoWidth: false,
                    responsive: true,
                    columnDefs: [{
                            width: "120px",
                            targets: 1
                        },
                        {
                            width: "120px",
                            targets: 2
                        }
                    ]
                });
            }
        });
    </script>