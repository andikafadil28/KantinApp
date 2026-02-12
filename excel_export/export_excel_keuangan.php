<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

include "../Database/connect.php";
require '../vendor/autoload.php';

// Inisialisasi variabel filter dari POST
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$kios_filter = isset($_POST['kios_filter']) ? $_POST['kios_filter'] : '';
$where_clause = "";

// 1. Sanitasi input filter
$start_date_esc = $conn->real_escape_string($start_date);
$end_date_esc = $conn->real_escape_string($end_date);
$kios_filter_esc = $conn->real_escape_string($kios_filter);

// Logika penentuan klausa WHERE (Disamakan dengan laporan.php)
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

// 2. Query Data dari Database (Disamakan dengan laporan_keuangan.php)
$query_string = "SELECT
tb_order.waktu_order AS Waktu_Order,tb_menu.nama AS Nama_Menu,tb_list_order.jumlah AS Jumlah_Terjual,
tb_order.nama_kios AS Nama_Toko,

tb_menu.harga+tb_menu.pajak AS Harga_Jual_Per_Menu,(tb_menu.harga+tb_menu.pajak)*0.11 AS Harga_PPN, (tb_menu.harga+tb_menu.pajak)+(tb_menu.harga+tb_menu.pajak)*0.11 AS Harga_Pembeli_Per_Menu,

(tb_menu.harga+tb_menu.pajak)*tb_list_order.jumlah AS Harga_Total_Per_Menu,
((tb_menu.harga+tb_menu.pajak)*tb_list_order.jumlah)*0.11 AS Harga_Total_PPN,
((tb_menu.harga+tb_menu.pajak)*tb_list_order.jumlah) + (((tb_menu.harga+tb_menu.pajak)*tb_list_order.jumlah)*0.11) AS Harga_Pembeli_Total,

tb_menu.harga * tb_list_order.jumlah AS Keuntungan_Toko,
tb_menu.pajak * tb_list_order.jumlah AS Keuntungan_RS,
(tb_menu.pajak * tb_list_order.jumlah) + (((tb_menu.harga+tb_menu.pajak)*0.11)*tb_list_order.jumlah) AS Keuntungan_RS_Pajak

FROM tb_list_order
RIGHT join tb_order on tb_order.id_order = tb_list_order.kode_order
LEFT JOIN tb_menu on tb_menu.id = tb_list_order.menu
$where_clause
ORDER BY `tb_order`.`waktu_order` DESC";


$result = $conn->query($query_string);
if (!$result) {
    die("Query Error: " . $conn->error);
}

// Hitung total yang relevan
$total_harga_total = 0;
$total_ppn = 0;
$total_pembeli_total = 0;
$total_keuntungan_toko = 0;
$total_keuntungan_rs = 0;
$total_keuntungan_rs_pajak = 0;
$data = [];
while ($row = $result->fetch_assoc()) {
    $total_harga_total += $row['Harga_Total_Per_Menu'] ?? 0;
    $total_ppn += $row['Harga_Total_PPN'] ?? 0;
    $total_pembeli_total += $row['Harga_Pembeli_Total'] ?? 0;
    $total_keuntungan_toko += $row['Keuntungan_Toko'] ?? 0;
    $total_keuntungan_rs += $row['Keuntungan_RS'] ?? 0;
    $total_keuntungan_rs_pajak += $row['Keuntungan_RS_Pajak'] ?? 0;
    $data[] = $row;
}

// --- Bagian Styling dan Excel Generation ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Keuangan Detail');

// Tentukan Nama Kolom Terakhir (N)
$last_col = 'N';

// Tentukan Nama Toko untuk Judul Laporan dan Nama File
$nama_toko_judul = ($kios_filter && $kios_filter != 'all') ? strtoupper($kios_filter) : 'SEMUA TOKO';

// 1. JUDUL LAPORAN (Baris 1 & 2)
$sheet->mergeCells('A1:' . $last_col . '1');
$sheet->setCellValue('A1', 'LAPORAN DETAIL KEUANGAN');
$sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:' . $last_col . '2');
// Tambahkan informasi rentang tanggal pada judul jika ada filter tanggal
$tanggal_info_judul = "";
if (!empty($start_date) && !empty($end_date)) {
    $tanggal_info_judul = " | TANGGAL: " . $start_date . " s/d " . $end_date;
} else if (!empty($start_date)) {
    $tanggal_info_judul = " | MULAI TANGGAL: " . $start_date;
} else if (!empty($end_date)) {
    $tanggal_info_judul = " | SAMPAI TANGGAL: " . $end_date;
}
$sheet->setCellValue('A2', 'TOKO: ' . $nama_toko_judul . $tanggal_info_judul);
$sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Tentukan baris awal untuk Header Tabel
$header_row = 4; // Dimulai di baris 4 karena baris 1-3 untuk Judul/Info
$data_start_row = 5;

// 2. HEADER TABEL (Baris 4)
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'], // Teks Putih
        'size' => 12,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '0070C0'], // Background Biru
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
// Styling untuk baris 4 (Header)
$sheet->getStyle('A' . $header_row . ':' . $last_col . $header_row)->applyFromArray($headerStyle);

// Tulis Header di baris 4 (Disamakan dengan kolom di laporan_keuangan.php)
$sheet->setCellValue('A' . $header_row, 'No');
$sheet->setCellValue('B' . $header_row, 'Waktu Order');
$sheet->setCellValue('C' . $header_row, 'Nama Menu');
$sheet->setCellValue('D' . $header_row, 'Jumlah Terjual');
$sheet->setCellValue('E' . $header_row, 'Nama Toko');
$sheet->setCellValue('F' . $header_row, 'Harga Jual/Menu');
$sheet->setCellValue('G' . $header_row, 'PPN/Menu');
$sheet->setCellValue('H' . $header_row, 'Harga Pembeli/Menu');
$sheet->setCellValue('I' . $header_row, 'Harga Total/Menu');
$sheet->setCellValue('J' . $header_row, 'Total PPN');
$sheet->setCellValue('K' . $header_row, 'Harga Pembeli Total');
$sheet->setCellValue('L' . $header_row, 'Keuntungan Toko');
$sheet->setCellValue('M' . $header_row, 'Keuntungan RS');
$sheet->setCellValue('N' . $header_row, 'Keuntungan RS + Pajak');

// 3. DATA ROWS (Mulai Baris 5)
$rowNum = $data_start_row;
$id_nomor = 1;
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'DDDDDD'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

foreach ($data as $row) {
    // Terapkan warna selang-seling (Zebra Stripes)
    if ($rowNum % 2 != 0) { // Ganti ke ganjil/odd untuk menyesuaikan hitungan baris Excel
        $sheet->getStyle('A' . $rowNum . ':' . $last_col . $rowNum)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
    }
    
    $sheet->setCellValue('A' . $rowNum, $id_nomor++);
    $sheet->setCellValue('B' . $rowNum, $row['Waktu_Order']);
    $sheet->setCellValue('C' . $rowNum, $row['Nama_Menu']);
    $sheet->setCellValue('D' . $rowNum, $row['Jumlah_Terjual'] ?? 0);
    $sheet->setCellValue('E' . $rowNum, $row['Nama_Toko']);
    $sheet->setCellValue('F' . $rowNum, $row['Harga_Jual_Per_Menu'] ?? 0);
    $sheet->setCellValue('G' . $rowNum, $row['Harga_PPN'] ?? 0);
    $sheet->setCellValue('H' . $rowNum, $row['Harga_Pembeli_Per_Menu'] ?? 0);
    $sheet->setCellValue('I' . $rowNum, $row['Harga_Total_Per_Menu'] ?? 0);
    $sheet->setCellValue('J' . $rowNum, $row['Harga_Total_PPN'] ?? 0);
    $sheet->setCellValue('K' . $rowNum, $row['Harga_Pembeli_Total'] ?? 0);
    $sheet->setCellValue('L' . $rowNum, $row['Keuntungan_Toko'] ?? 0);
    $sheet->setCellValue('M' . $rowNum, $row['Keuntungan_RS'] ?? 0);
    $sheet->setCellValue('N' . $rowNum, $row['Keuntungan_RS_Pajak'] ?? 0);
    
    // Terapkan border dan alignment untuk baris data
    $sheet->getStyle('A' . $rowNum . ':' . $last_col . $rowNum)->applyFromArray($dataStyle);

    // Format mata uang (Kolom F sampai N)
    $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('H' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('K' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('L' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('M' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('N' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    
    // Rata kiri untuk teks (Kolom C, E)
    $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $rowNum++;
}

// 4. TOTAL ROW (Baris terakhir data + 1)

// Styling untuk Baris Total 1 (Ringkasan Keuntungan)
$totalRowStyle1 = [
    'font' => [
        'bold' => true,
        'size' => 13,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'DDEBF7'],
    ],
    'borders' => [
        'top' => ['borderStyle' => Border::BORDER_THICK],
        'bottom' => ['borderStyle' => Border::BORDER_THIN],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

// Baris Ringkasan Total
$sheet->getStyle('A' . $rowNum . ':' . $last_col . $rowNum)->applyFromArray($totalRowStyle1);

// Gabungkan kolom A sampai E untuk teks 'TOTAL'
$sheet->mergeCells('A' . $rowNum . ':E' . $rowNum);
$sheet->setCellValue('A' . $rowNum, 'TOTAL');
$sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Nilai Total (kolom perhitungan)
$sheet->setCellValue('I' . $rowNum, $total_harga_total);
$sheet->setCellValue('J' . $rowNum, $total_ppn);
$sheet->setCellValue('K' . $rowNum, $total_pembeli_total);
$sheet->setCellValue('L' . $rowNum, $total_keuntungan_toko);
$sheet->setCellValue('M' . $rowNum, $total_keuntungan_rs);
$sheet->setCellValue('N' . $rowNum, $total_keuntungan_rs_pajak);

// Format mata uang untuk Total
$sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle('K' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle('L' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle('M' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle('N' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');

$rowNum++;

// Styling untuk Baris Total 2 (Total Pembeli + Keuntungan)
$totalRowStyle2 = [
    'font' => [
        'bold' => true,
        'size' => 13,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D0E8D0'], // Warna Hijau Muda untuk Grand Total
    ],
    'borders' => [
        'top' => ['borderStyle' => Border::BORDER_THICK],
        'bottom' => ['borderStyle' => Border::BORDER_THICK],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

$sheet->getStyle('A' . $rowNum . ':' . $last_col . $rowNum)->applyFromArray($totalRowStyle2);

// Gabungkan kolom A sampai D untuk teks 'Total Pendapatan'
$sheet->mergeCells('A' . $rowNum . ':E' . $rowNum);
$sheet->setCellValue('A' . $rowNum, 'GRAND TOTAL (Pembeli Total + Keuntungan RS Pajak)');
$sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Nilai Grand Total
$grand_total = $total_pembeli_total + $total_keuntungan_rs_pajak;
$sheet->setCellValue('F' . $rowNum, $grand_total);
$sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0'); // Format mata uang

// Gabungkan kolom F sampai N untuk kolom Grand Total
$sheet->mergeCells('F' . $rowNum . ':' . $last_col . $rowNum); 


// 5. Set Lebar Kolom Otomatis
foreach (range('A', $last_col) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}


// --- Pengaturan Download File Excel ---
// 1. Ambil Tanggal Hari Ini
$tanggal_hari_ini = date('Y-m-d'); 

// 2. Ambil Nama Toko (dibersihkan dari spasi/karakter khusus)
$nama_toko_file = str_replace([' ', '/', '\\'], '-', $nama_toko_judul); 

// 3. Buat Nama File Akhir
$filename = "laporan-detail-pembayaran-" . strtolower($nama_toko_file) . "-" . $tanggal_hari_ini . ".xlsx";


// Set header untuk download file excel
if (ob_get_length()) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
