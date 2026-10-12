<?php
session_start();

if (empty($_SESSION["username_kantin"])) {
    header('Location: login');
    exit();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="template_import_menu.csv"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM agar Excel membaca UTF-8 dengan benar

fputcsv($out, ['nama', 'keterangan', 'kategori', 'harga', 'margin', 'kios', 'status']);
fputcsv($out, ['Contoh Nasi Goreng', 'Nasi goreng spesial', 'Makanan', '15000', '3000', 'Nama Kios', 'aktif']);
fputcsv($out, ['Contoh Es Teh', 'Es teh manis', 'Minuman', '5000', '1000', 'Nama Kios', 'aktif']);

fclose($out);
exit();
