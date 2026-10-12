<?php
session_start();

include("../Database/connect.php");

if (!isset($_SESSION['level_kantin']) || (int) $_SESSION['level_kantin'] !== 1) {
    echo "<script>alert('Akses ditolak'); window.location.href='../menu';</script>";
    exit();
}

if (!isset($_POST['input_menu_import_proses'])) {
    header('Location: ../menu');
    exit();
}

function norm_key($value)
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value); // strip BOM
    $value = strtolower(trim($value));
    return preg_replace('/[^a-z0-9]+/', '', $value);
}

function clean_text($value)
{
    $value = (string) $value;
    if ($value === '') {
        return '';
    }

    // File dari Excel/CSV lama bisa ber-encoding Windows-1252 (byte 0xA0 = NBSP).
    // Byte itu invalid di utf8mb4 dan bikin "Incorrect string value".
    if (!mb_check_encoding($value, 'UTF-8')) {
        $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

    // Normalisasi spasi non-standar jadi spasi biasa.
    $value = preg_replace('/\x{00A0}|\x{2007}|\x{202F}|\x{FEFF}/u', ' ', $value);
    // Buang karakter kontrol (kecuali tab & newline).
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

    return trim($value);
}

function parse_csv_rows($path)
{
    $rows = [];
    $handle = fopen($path, 'r');
    if ($handle === false) {
        return $rows;
    }

    // Auto-deteksi delimiter (Excel sering pakai ';' atau tab).
    $delimiter = ',';
    $first_line = fgets($handle);
    if ($first_line !== false) {
        $counts = [
            ',' => substr_count($first_line, ','),
            ';' => substr_count($first_line, ';'),
            "\t" => substr_count($first_line, "\t"),
        ];
        arsort($counts);
        $delimiter = array_key_first($counts);
    }
    rewind($handle);

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rows[] = $row;
    }
    fclose($handle);
    return $rows;
}

function dedup_key($nama, $kios)
{
    return strtolower(trim($nama)) . "\0" . strtolower(trim($kios));
}

function resolve_status($value)
{
    $status = strtolower(trim((string) $value));
    if ($status === '') {
        return 1;
    }
    return in_array($status, ['1', 'aktif', 'ya', 'yes', 'true', 'y', 'a'], true) ? 1 : 0;
}

$file = $_FILES['file_import'] ?? null;
if (
    !$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK
    || !is_uploaded_file($file['tmp_name'])
) {
    echo "<script>alert('File tidak valid atau gagal diupload'); window.location.href='../menu';</script>";
    exit();
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
    echo "<script>alert('Format harus CSV, XLSX, atau XLS'); window.location.href='../menu';</script>";
    exit();
}

$rows = [];
try {
    if ($ext === 'csv') {
        $rows = parse_csv_rows($file['tmp_name']);
    } else {
        require '../vendor/autoload.php';
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
    }
} catch (\Throwable $e) {
    echo "<script>alert('Gagal membaca file. Pastikan format benar.'); window.location.href='../menu';</script>";
    exit();
}

// Bersihkan encoding tiap cell sebelum diproses.
$rows = array_map(function ($row) {
    return is_array($row) ? array_map('clean_text', $row) : $row;
}, $rows);

// Buang baris kosong di akhir.
$rows = array_values(array_filter($rows, function ($row) {
    return is_array($row) && count(array_filter($row, function ($cell) {
        return trim((string) $cell) !== '';
    })) > 0;
}));

if (count($rows) < 2) {
    echo "<script>alert('File kosong atau hanya berisi header'); window.location.href='../menu';</script>";
    exit();
}

if (count($rows) > 5001) {
    echo "<script>alert('Terlalu banyak baris (maksimal 5000 menu sekali import)'); window.location.href='../menu';</script>";
    exit();
}

$header = array_shift($rows);

$aliases = [
    'nama' => ['nama', 'namamenu', 'menu', 'namamakanan'],
    'keterangan' => ['keterangan', 'ket', 'deskripsi', 'catatan'],
    'kategori' => ['kategori', 'kategorimenu', 'jenis', 'jenismenu'],
    'harga' => ['harga', 'hargajual', 'hargamenu'],
    'margin' => ['margin', 'pajak', 'stok', 'marginrs'],
    'kios' => ['kios', 'toko', 'namatoko'],
    'status' => ['status', 'statusaktif', 'aktif'],
];

$columns = [];
foreach ($header as $index => $cell) {
    $key = norm_key($cell);
    foreach ($aliases as $field => $names) {
        if (in_array($key, $names, true) && !isset($columns[$field])) {
            $columns[$field] = $index;
        }
    }
}

if (!isset($columns['nama'])) {
    echo "<script>alert('Kolom \"nama\" tidak ditemukan pada header'); window.location.href='../menu';</script>";
    exit();
}

$col = function ($row, $field) use ($columns) {
    if (!isset($columns[$field])) {
        return '';
    }
    $index = $columns[$field];
    return isset($row[$index]) ? trim((string) $row[$index]) : '';
};

// Preload referensi kategori, kios aktif, dan nama menu existing.
$kategori_by_name = [];
$kategori_ids = [];
$res = mysqli_query($conn, "SELECT id_kategori, kategori_menu FROM tb_kategori_menu");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $kategori_by_name[norm_key($r['kategori_menu'])] = $r['id_kategori'];
        $kategori_ids[(string) $r['id_kategori']] = true;
    }
}

$kios_valid = [];
$res = mysqli_query($conn, "SELECT nama FROM tb_kios WHERE status = 1");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $kios_valid[strtolower(trim($r['nama']))] = $r['nama'];
    }
}

$existing = [];
$res = mysqli_query($conn, "SELECT nama, nama_toko FROM tb_menu");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $existing[dedup_key($r['nama'], $r['nama_toko'])] = true;
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

foreach ($rows as $offset => $row) {
    $line_no = $offset + 2; // +2: header baris 1, data mulai baris 2
    $nama = $col($row, 'nama');
    if ($nama === '') {
        continue;
    }

    $keterangan = $col($row, 'keterangan');
    $kategori_raw = $col($row, 'kategori');
    $harga = $col($row, 'harga');
    $margin = $col($row, 'margin');
    $kios_raw = $col($row, 'kios');
    $status = resolve_status($col($row, 'status'));

    // Resolve kategori: id langsung atau nama kategori.
    $kategori_id = null;
    if ($kategori_raw !== '') {
        if (ctype_digit($kategori_raw) && isset($kategori_ids[$kategori_raw])) {
            $kategori_id = (int) $kategori_raw;
        } elseif (isset($kategori_by_name[norm_key($kategori_raw)])) {
            $kategori_id = (int) $kategori_by_name[norm_key($kategori_raw)];
        }
    }
    if ($kategori_id === null) {
        $errors[] = "baris $line_no (kategori)";
        continue;
    }

    if (!is_numeric($harga) || !is_numeric($margin)) {
        $errors[] = "baris $line_no (harga/margin)";
        continue;
    }

    $kios_nama = $kios_valid[strtolower($kios_raw)] ?? null;
    if ($kios_nama === null) {
        $errors[] = "baris $line_no (kios)";
        continue;
    }

    $key = dedup_key($nama, $kios_nama);
    if (isset($existing[$key])) {
        $skipped++;
        continue;
    }

    $harga_val = (float) $harga;
    $margin_val = (float) $margin;
    $kategori_str = (string) $kategori_id;

    mysqli_stmt_bind_param(
        $insert_stmt,
        "ssssddi",
        $nama,
        $keterangan,
        $kategori_str,
        $kios_nama,
        $harga_val,
        $margin_val,
        $status
    );

    $executed = false;
    try {
        $executed = mysqli_stmt_execute($insert_stmt);
    } catch (\mysqli_sql_exception $e) {
        $executed = false; // satu baris gagal jangan menggagalkan seluruh import
    }

    if ($executed) {
        $inserted++;
        $existing[$key] = true;
    } else {
        $errors[] = "baris $line_no (gagal simpan)";
    }
}

mysqli_stmt_close($insert_stmt);

$msg = "$inserted menu ditambahkan.";
if ($skipped > 0) {
    $msg .= " $skipped dilewati (nama sudah ada).";
}
if (count($errors) > 0) {
    $preview = implode(', ', array_slice($errors, 0, 5));
    if (count($errors) > 5) {
        $preview .= ', ...';
    }
    $msg .= " " . count($errors) . " gagal: $preview";
}

echo "<script>alert(" . json_encode($msg) . "); window.location.href='../menu';</script>";
exit();
