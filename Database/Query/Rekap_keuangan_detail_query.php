<?php
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
ORDER BY `tb_order`.`waktu_order` DESC";
