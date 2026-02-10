<?php
$data = mysqli_query($conn, "
 SELECT tb_menu.nama AS nama_menu, sum(tb_list_order.jumlah) AS Total_Terjual, tb_menu.harga AS harga_satuan, SUM(tb_list_order.jumlah)*tb_menu.harga as Total_harga, tb_menu.nama_toko 
                    FROM tb_order
                    LEFT JOIN user ON user.id = tb_order.kasir
                    LEFT JOIN tb_list_order ON tb_list_order.kode_order = tb_order.id_order
                    LEFT JOIN tb_menu ON tb_menu.id = tb_list_order.menu
                    LEFT JOIN tb_bayar ON tb_bayar.id_bayar = tb_list_order.kode_order
                    WHERE tb_order.waktu_order >= CURDATE()
                    AND tb_order.waktu_order < CURDATE() + INTERVAL 1 DAY
                    GROUP BY tb_menu.nama, tb_menu.nama_toko
                    ORDER BY Total_Terjual DESC;");

$menu = [];
$total = [];

while ($row = mysqli_fetch_assoc($data)) {
      $menu[] = $row['nama_menu'];
      $total[] = (int)$row['Total_Terjual'];
}
