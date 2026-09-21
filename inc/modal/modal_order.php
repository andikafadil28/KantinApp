<!-- Modal tambah order -->
                <div class="modal fade" id="ModalTambah" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Makanan Dan Minuman</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate action="validate/validate_input_order.php" method="post">
                                    <div class="row mt-3">
                                        <div class="col-lg-3">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingkodeorder" placeholder="" name="kode_order" value="<?php echo date('ymdHi') . rand(100, 999) ?>" readonly>
                                                <label for="floatingkodeorder">Kode Order</label>
                                                <div class="invalid-feedback">
                                                    Masukan Kode Order
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingmeja" placeholder="nomor meja" name="meja" required>
                                                <label for="floatingmeja">Meja</label>
                                                <div class="invalid-feedback">
                                                    Meja tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingpelanggan" placeholder="nama pelanggan" name="pelanggan" required>
                                                <label for="floatingpelanggan">Pelanggan</label>
                                                <div class="invalid-feedback">
                                                    Nama Pelanggan tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-floating">
                                                <select class="form-select" aria-label="Default select example" name="kios" required>
                                                    <option selected hidden value="">Pilih Kios User</option>
                                                    <?php
                                                    foreach ($result2 as $row2) {
                                                        if ($row2['status'] == 1) {
                                                    ?>
                                                            <option value="<?php echo $row2['nama'] ?>"><?php echo $row2['nama'] ?></option>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <label for="floatingKios">Kios</label>
                                                <div class="invalid-feedback">
                                                    Kios tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="catatan" placeholder="Masukan Catatan Jika Ada" name="catatan">
                                                <label for="catatan">Catatan</label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" name="input_order_proses">Buat Order</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (empty($result)) {
                    echo "<div class='alert alert-warning'>Data tidak ditemukan</div>";
                } else {
                    foreach ($result as $row) {
                ?>
                        <!-- Modal edit -->
                        <div class="modal fade" id="ModalEdit<?php echo $row['id_order'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Makanan Dan Minuman</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form class="needs-validation" novalidate action="validate/validate_edit_order.php" method="post">
                                            <div class="row mt-3">
                                                <div class="col-lg-3">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingkodeorder" placeholder="" name="kode_order" value="<?php echo $row['id_order'] ?>" readonly>
                                                        <label for="floatingkodeorder">Kode Order</label>
                                                        <div class="invalid-feedback">
                                                            Masukan Kode Order
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingmeja" placeholder="nomor meja" name="meja" value="<?php echo $row['meja'] ?>" required>
                                                        <label for="floatingmeja">Meja</label>
                                                        <div class="invalid-feedback">
                                                            Meja tidak boleh kosong
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-5">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingpelanggan" placeholder="nama pelanggan" name="pelanggan" value="<?php echo $row['pelanggan'] ?>" required>
                                                        <label for="floatingpelanggan">Pelanggan</label>
                                                        <div class="invalid-feedback">
                                                            Nama Pelanggan tidak boleh kosong
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="form-floating">
                                                        <select class="form-select" aria-label="Default select example" name="kios" required>
                                                            <option selected hidden value="<?php echo $row['nama_kios'] ?>"><?php echo $row['nama_kios'] ?></option>
                                                            <?php
                                                            foreach ($result2 as $row2) {
                                                                if ($row2['status'] == 1) {
                                                            ?>
                                                                    <option value="<?php echo $row2['nama'] ?>"><?php echo $row2['nama'] ?></option>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                        <label for="floatingKios">Kios</label>
                                                        <div class="invalid-feedback">
                                                            Kios tidak boleh kosong
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-lg-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="catatan" placeholder="Masukan Catatan Jika Ada" name="catatan" value="<?php echo $row['catatan'] ?>" required>
                                                        <label for="catatan">Catatan</label>
                                                        <div class="invalid-feedback">
                                                            Catatan tidak boleh kosong
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary" name="input_order_edit">Buat Order</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal kosongkan item order -->
                        <div class="modal fade" id="ModalClearItems<?php echo $row['id_order'] ?>" tabindex="-1" aria-labelledby="clearItemsLabel<?php echo $row['id_order'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-fullscreen-md-down">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="clearItemsLabel<?php echo $row['id_order'] ?>">Kosongkan Item Order</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="validate/validate_clear_order_items.php" method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_order" value="<?php echo $row['id_order'] ?>">
                                            <p class="mb-2">Hapus semua item order atas nama <strong><?php echo $row['pelanggan'] ?></strong> dengan kode order <strong><?php echo $row['id_order'] ?></strong>?</p>
                                            <div class="alert alert-warning mb-0" role="alert">
                                                Data order dan pembayaran tidak ikut dihapus.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger" name="clear_order_items">Kosongkan Item</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End modal kosongkan item order -->

                        <!-- Modal delete -->
                        <div class="modal fade" id="ModalDelete<?php echo $row['id_order'] ?>" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Delete</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form class="needs-validation" novalidate action="validate/validate_delete_order.php" method="post">
                                            <input type="hidden" name="id_order" value="<?php echo $row['id_order'] ?>">

                                            <div class="col-lg-12">

                                                <h5>Apakah anda yakin ingin menghapus order atas nama <b><?php echo $row['pelanggan'] ?> dengan kode order <?php echo $row['id_order'] ?> ?</b></h5>


                                            </div>


                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger" name="input_order_delete">Hapus Data</button>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- End Modal delete -->
                <?php
                    }
                }
                ?>
