<!-- Modal tambah item -->
<div class="modal fade" id="tambahItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
      aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-fullscreen-md-down">
            <div class="modal-content">
                  <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Makanan Dan Minuman</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                        <form class="needs-validation" novalidate action="validate/validate_order_item.php"
                              method="post">
                              <input type="hidden" name="kode_order" value="<?php echo $kode ?>">
                              <input type="hidden" name="meja" value="<?php echo $meja ?>">
                              <input type="hidden" name="pelanggan" value="<?php echo $customer ?>">
                              <input type="hidden" name="kios" value="<?php echo $toko ?>">
                              <div class="row mt-3">
                                    <div class="col-lg-6">
                                          <div class="form-floating mb-3">
                                                <select class="form-select select2" name="menu" id="menu-pilihan"
                                                      data-placeholder="Pilih Menu" style="width:100%">
                                                      <option value=""></option>
                                                      <?php
                                                      foreach ($set_menu as $value) {
                                                            ?>
                                                            <option value="<?php echo $value['id'] ?>">
                                                                  <?php echo $value['nama'] ?>
                                                            </option>
                                                            <?php
                                                      }
                                                      ?>
                                                </select>

                                          </div>
                                    </div>

                              </div>

                              <div class="row mb-3">
                                    <div class="col-lg-4">
                                          <div class="form-floating">
                                                <input type="number" class="form-control" id="floatingJumlah"
                                                      placeholder="Masukan Jumlah" name="jumlah" required>
                                                <label for="floatingJumlah">Jumlah Porsi</label>
                                                <div class="invalid-feedback">
                                                      Jumlah tidak boleh kosong
                                                </div>
                                          </div>
                                    </div>
                              </div>

                              <!-- Pastikan jQuery dan Select2 CSS/JS sudah diload di layout (CDN atau lokal) -->
                              <script>
                                    (function ($) {
                                          $(function () {
                                                function initMenuSelect($modal) {
                                                      var $sel = $modal.find('#menu-pilihan');
                                                      if ($sel.length && !$sel.hasClass('select2-initialized')) {
                                                            $sel.select2({
                                                                  placeholder: $sel.data('placeholder') || 'Pilih Menu',
                                                                  allowClear: true,
                                                                  width: '100%',
                                                                  dropdownParent: $modal.find('.modal-content')
                                                            });
                                                            $sel.addClass('select2-initialized');
                                                      }
                                                }

                                                // Inisialisasi saat modal dibuka
                                                $('#tambahItem').on('shown.bs.modal', function () {
                                                      initMenuSelect($(this));
                                                });

                                                // Jika modal mungkin sudah terbuka saat load
                                                initMenuSelect($('#tambahItem'));
                                          });
                                    })(jQuery);
                              </script>
                              <div class="row mb-3">
                                    <div class="col">
                                          <div class="form-floating">
                                                <input type="text" class="form-control" id="catatan_order"
                                                      placeholder="Masukan Keterangan" name="catatan_order">
                                                <label for="catatan_order">Catatan</label>
                                          </div>
                                    </div>
                              </div>

                              <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                          data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary"
                                          name="input_order_item_proses">Simpan</button>
                              </div>
                        </form>
                  </div>
            </div>
      </div>
</div>
<!-- Modal bayar -->

<?php
if (empty($result)) {
      echo "<div class='alert alert-warning'>Data tidak ditemukan</div>";
} else {
      foreach ($result as $row) {
            ?>
            <!-- Modal edit -->
            <div class="modal fade" id="ModalEdit<?php echo $row['id_list_order'] ?>" tabindex="-1"
                  aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                        <div class="modal-content">
                              <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Edit Item</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                    <form class="needs-validation" novalidate action="validate/validate_edit_order_item.php"
                                          method="post">
                                          <input type="hidden" name="id_list_order" value="<?php echo $row['id_list_order'] ?>">
                                          <input type="hidden" name="kode_order" value="<?php echo $kode ?>">
                                          <input type="hidden" name="meja" value="<?php echo $meja ?>">
                                          <input type="hidden" name="pelanggan" value="<?php echo $customer ?>">
                                          <input type="hidden" name="kios" value="<?php echo $toko ?>">
                                          <div class="row mt-3">
                                                <div class="col-lg-6">
                                                      <div class="form-floating mb-3">
                                                            <select class="form-select" name="menu" id="">
                                                                  <option selected hidden value="">Pilih Menu</option>
                                                                  <?php
                                                                  foreach ($set_menu as $value) {
                                                                        if ($row['menu'] == $value['id']) {
                                                                              echo "<option selected value='" . $value['id'] . "'>" . $value['nama'] . "</option>";
                                                                        } else {
                                                                              echo "<option value='" . $value['id'] . "'>" . $value['nama'] . "</option>";
                                                                        }
                                                                        ?>
                                                                        <?php
                                                                  }
                                                                  ?>
                                                            </select>
                                                            <label for="menu">Menu Makanan/Minuman</label>
                                                            <div class="invalid-feedback">
                                                                  Pilih Menu
                                                            </div>
                                                      </div>
                                                </div>
                                                <div class="col-lg-4">
                                                      <div class="form-floating">
                                                            <input type="number" class="form-control" id="floatingJumlahEdit"
                                                                  placeholder="Masukan Jumlah" name="jumlah"
                                                                  value="<?php echo $row['jumlah'] ?>" required>
                                                            <label for="floatingJumlahEdit">Jumlah Porsi</label>
                                                            <div class="invalid-feedback">
                                                                  Jumlah tidak boleh kosong
                                                            </div>
                                                      </div>
                                                </div>
                                          </div>
                                          <div class="row mb-3">
                                                <div class="col">
                                                      <div class="form-floating">
                                                            <input type="text" class="form-control" id="catatan_order"
                                                                  placeholder="Masukan Keterangan" name="catatan_order"
                                                                  value="<?php echo $row['catatan_order'] ?>">
                                                            <label for="catatan_order_edit">Catatan</label>
                                                      </div>
                                                </div>

                                          </div>
                                          <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                      data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary" name="edit_order_item">Simpan</button>
                                          </div>
                                    </form>
                              </div>
                        </div>
                  </div>
            </div>



            <!-- Modal delete -->
            <div class="modal fade" id="ModalDelete<?php echo $row['id_list_order'] ?>" tabindex="-1"
                  aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                              <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Hapus Item</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus item ini?</p>
                                    <form action="validate/validate_delete_order_item.php" method="post">
                                          <input type="hidden" name="id_list_order" value="<?php echo $row['id_list_order'] ?>">
                                          <input type="hidden" name="kode_order" value="<?php echo $kode ?>">
                                          <input type="hidden" name="meja" value="<?php echo $meja ?>">
                                          <input type="hidden" name="pelanggan" value="<?php echo $customer ?>">
                                          <input type="hidden" name="kios" value="<?php echo $toko ?>">
                                          <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                      data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger" name="delete_order_item">Hapus</button>
                                          </div>
                                    </form>
                              </div>
                        </div>
                  </div>
            </div>




            <?php
      }
      ?>
      <!-- Modal bayar -->
      <div class="modal fade" id="bayar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                  <div class="modal-content">
                        <div class="modal-header">
                              <h1 class="modal-title fs-5" id="staticBackdropLabel">Bayar</h1>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                              <div class="table-responsive-lg-12">
                                    <table class="table table-hover">
                                          <thead>
                                                <tr>
                                                      <th scope="col">Menu</th>
                                                      <th scope="col">Harga</th>
                                                      <th scope="col">Qty</th>
                                                      <th scope="col">Catatan</th>
                                                      <th scope="col">Total</th>
                                                </tr>
                                          </thead>
                                          <tbody>
                                                <?php

                                                foreach ($result as $row) {
                                                      ?>
                                                      <tr>
                                                            <td><?php echo $row['nama'] ?></td>
                                                            <td><?php echo number_format($row['harga_jual'], 0, ',', '.') ?></td>
                                                            <td><?php echo $row['jumlah'] ?></td>
                                                            <td><?php echo $row['catatan_order'] ?></td>
                                                            <td><?php echo number_format($row['harganya'], 0, ',', '.') ?></td>
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
                                                            $total2 = 0;
                                                            $total3 = 0;
                                                            foreach ($result as $row) {
                                                                  $total += $row['harganya'];
                                                                  $total2 += $row['harganya_toko'];
                                                                  $total3 += $row['ppn_pajak'];
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
                                                            if ($diskon_nominal < 0)
                                                                  $diskon_nominal = 0;
                                                            if ($diskon_nominal > $total)
                                                                  $diskon_nominal = $total;

                                                            $grand_total = $total - $diskon_nominal;
                                                            ?>

                                                            <div>Diskon:
                                                                  -<?php echo number_format($diskon_nominal + $diskon, 0, ',', '.'); ?>
                                                            </div>
                                                            <div>Total Harga:
                                                                  <?php echo number_format($grand_total - $diskon, 0, ',', '.'); ?>
                                                            </div>
                                                            <?php
                                                            $grand_total = $grand_total - $diskon;
                                                            $ppn = $total3;
                                                            // $grand_total += $ppn;
                                                            ?>
                                                            <div>PPN 11%: <?php echo number_format($ppn, 0, ',', '.'); ?>
                                                            </div>
                                                            <div class="fw-bold">Grand Total:
                                                                  <?php echo number_format($grand_total, 0, ',', '.'); ?>
                                                            </div>
                                                      </td>
                                                </tr>
                                          </tbody>
                                    </table>

                              </div>
                              <span class="text-danger fs-h fw-semibold">Apakah anda yakin ingin melakukan pembayaran?</span>
                              <form class="needs-validation" novalidate action="validate/validate_bayar.php" method="post">
                                    <input type="hidden" name="kode_order" value="<?php echo $kode ?>">
                                    <input type="hidden" name="meja" value="<?php echo $meja ?>">
                                    <input type="hidden" name="pelanggan" value="<?php echo $customer ?>">
                                    <input type="hidden" name="kios" value="<?php echo $toko ?>">
                                    <input type="hidden" name="total_bayar" value="<?php echo $total ?>">
                                    <input type="hidden" name="grand_total" value="<?php echo $grand_total ?>">
                                    <input type="hidden" name="diskon" value="<?php echo $diskon_nominal ?>">
                                    <input type="hidden" name="harga_toko" value="<?php echo $total2 ?>">
                                    <input type="hidden" name="ppn" value="<?php echo $ppn ?>">
                                    <div class="row mt-3">
                                          <!-- <div class="col-lg-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="total_bayar" placeholder="Total Bayar" name="total_bayar" value="<?php echo number_format($total, 0, ',', '.') ?>" readonly>
                                                        <label for="total_bayar">Total Bayar</label>
                                                    </div>
                                                </div> -->
                                          <div class="col-lg-12">
                                                <div class="form-floating mb-3">
                                                      <input type="number" class="form-control" id="bayar"
                                                            value="<?php echo $grand_total ?>"
                                                            placeholder="<?php echo $grand_total ?>" name="bayar" required>
                                                      <label for="bayar">Jumlah Bayar</label>
                                                      <div class="invalid-feedback">
                                                            Jumlah bayar tidak boleh kosong
                                                      </div>
                                                </div>
                                          </div>
                                    </div>
                                    <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                          <button type="submit" class="btn btn-success" name="proses_bayar">Bayar</button>

                                    </div>
                              </form>
                        </div>
                  </div>
            </div>
      </div>



      <?php
}
?>