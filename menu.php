<?php
include "Database/connect.php";
$status_filter = isset($_POST['status_filter']) ? $_POST['status_filter'] : 'all';
$where_status = "";
if ($status_filter === '1' || $status_filter === '0') {
    $status_val = (int) $status_filter;
    $where_status = "AND tb_menu.status = $status_val";
}

$query = mysqli_query($conn, "select tb_menu.*, tb_kategori_menu.kategori_menu, tb_kategori_menu.jenis_menu from tb_menu
LEFT JOIN tb_kategori_menu ON tb_kategori_menu.id_kategori = tb_menu.kategori
LEFT JOIN tb_kios ON tb_kios.nama = tb_menu.nama_toko
WHERE tb_kios.status = 1
$where_status
ORDER BY tb_menu.status DESC, tb_menu.nama ASC");
$sel_kategori = mysqli_query($conn, "SELECT id_kategori,kategori_menu FROM tb_kategori_menu");
$query2 = mysqli_query($conn, "select * from tb_kios WHERE status = 1");
while ($record2 = mysqli_fetch_array($query2)) {
    $result2[] = $record2;
}
while ($record = mysqli_fetch_array($query)) {
    $result[] = $record;
}

?>

<!-- Conten -->
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-fork-knife"></i>
            Menu
        </div>
        <div class="card-body-scrollable">
            <div class="row">
                <?php
                if ($_SESSION["level_kantin"] == 1) {
                    ?>
                    <div class="col d-flex justify-content-end align-items-center gap-2 mb-3">
                        <form id="bulkDeleteForm" method="post" action="validate/validate_menu_bulk_delete.php"
                            onsubmit="return confirm('Hapus semua menu yang dicentang? Tindakan ini tidak bisa dibatalkan.')">
                            <button type="submit" class="btn btn-outline-danger" id="btnBulkDelete" disabled>
                                <i class="bi bi-trash"></i> Hapus Terpilih (<span id="bulkDeleteCount">0</span>)
                            </button>
                        </form>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#ModalImport">
                            <i class="bi bi-upload"></i> Import CSV/Excel</button>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ModalBulkTambah">
                            <i class="bi bi-list-plus"></i> Tambah Banyak</button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTambah">Tambah
                            Menu</button>
                    </div>
                    <?php
                } else {
                }
                ?>
                <div class="col-12 mb-3">
                    <form method="post" class="d-flex align-items-end gap-2">
                        <div>
                            <label for="status_filter" class="form-label mb-1">Filter Status</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>Semua</option>
                                <option value="1" <?php echo ($status_filter === '1') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo ($status_filter === '0') ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-secondary">Terapkan</button>
                        </div>
                    </form>
                </div>

                <!-- Modal tambah menu -->
                <div class="modal fade" id="ModalTambah" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Makanan Dan Minuman</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate action="validate/validate_menu.php"
                                    method="post" enctype="multipart/form-data">
                                    <div class="row mt-3">
                                        <div class="col lg-12">
                                            <div class="input-group">
                                                <input type="file" class="form-control py-9" id="floatingInputGambar"
                                                    placeholder="Masukan Gambar" name="foto">
                                                <label class="input-group-text" for="floatingInputGambar">Upload Foto
                                                    Menu</label>
                                            </div>
                                            <small class="text-muted">Foto opsional, boleh dikosongkan.</small>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingNama"
                                                    placeholder="Masukan Nama" name="nama_menu" required>
                                                <label for="floatingNama">Nama Makanan</label>
                                                <div class="invalid-feedback">
                                                    Nama tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingKeterangan"
                                                    placeholder="Masukan Keterangan" name="keterangan">
                                                <label for="floatingKeterangan">Keterangan</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col lg-4">
                                            <div class="form-floating mt-3">
                                                <select class="form-select" aria-label="Default select example"
                                                    name="kategori_menu" required>
                                                    <option selected hidden value="">Pilih Jenis Menu</option>
                                                    <?php
                                                    foreach ($sel_kategori as $row2) {
                                                        ?>
                                                        <option value="<?php echo $row2['id_kategori'] ?>">
                                                            <?php echo $row2['kategori_menu'] ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                                <label for="floatingKategori">Kategori Menu</label>
                                                <div class="invalid-feedback">
                                                    Jenis Menu tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col lg-4">
                                            <div class="form-floating mt-3">
                                                <input type="number" class="form-control" id="floatingHarga"
                                                    placeholder="Masukan Harga" name="harga" required>
                                                <label for="floatingHarga">Harga</label>
                                                <div class="invalid-feedback">
                                                    Harga tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col lg-4">
                                            <div class="form-floating mt-3">
                                                <input type="number" class="form-control" id="floatingStok"
                                                    placeholder="Masukan Stok" name="pajak" required>
                                                <label for="floatingStok">Margin</label>
                                                <div class="invalid-feedback">
                                                    Margin tidak boleh kosong
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col">
                                            <div class="form-floating mt-3">
                                                <select class="form-select" aria-label="Default select example"
                                                    name="kios" required>
                                                    <option selected hidden value="">Pilih Kios User</option>
                                                    <?php
                                                    foreach ($result2 as $row2) {
                                                        ?>
                                                        <option value="<?php echo $row2['nama'] ?>">
                                                            <?php echo $row2['nama'] ?>
                                                        </option>
                                                        <?php
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
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                    id="status_aktif" name="status_aktif" checked>
                                                <label class="form-check-label" for="status_aktif">
                                                    Aktifkan menu
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary"
                                            name="input_menu_proses">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal bulk tambah menu -->
                <?php
                if ($_SESSION["level_kantin"] == 1) {
                    ?>
                    <div class="modal fade" id="ModalBulkTambah" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="ModalBulkTambahLabel" aria-hidden="true">
                        <div class="modal-dialog modal-fullscreen">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="ModalBulkTambahLabel">Tambah Banyak Menu</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="needs-validation" novalidate action="validate/validate_menu_bulk.php"
                                        method="post" id="formBulkMenu">
                                        <div class="row g-2 align-items-end mb-2">
                                            <div class="col-12 col-md">
                                                <small class="text-muted d-block">Menu duplikat otomatis dilewati. Baris
                                                    tanpa nama diabaikan. Foto diisi belakangan lewat Edit.</small>
                                            </div>
                                            <div class="col-12 col-md-auto">
                                                <label for="bulkKiosGlobal" class="form-label mb-1 small">Isi kios untuk
                                                    semua baris <span class="text-muted">(opsional)</span></label>
                                                <div class="d-flex gap-2">
                                                    <select class="form-select form-select-sm" id="bulkKiosGlobal">
                                                        <option value="" selected hidden>Pilih Kios</option>
                                                        <?php
                                                        foreach ($result2 as $row2) {
                                                            ?>
                                                            <option value="<?php echo $row2['nama'] ?>">
                                                                <?php echo $row2['nama'] ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary text-nowrap"
                                                        id="btnTambahBaris"><i class="bi bi-plus-lg"></i> Tambah
                                                        Baris</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle" id="tableBulkMenu">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width:170px;">Nama <span
                                                                class="text-danger">*</span></th>
                                                        <th style="min-width:170px;">Keterangan</th>
                                                        <th style="min-width:150px;">Kategori <span
                                                                class="text-danger">*</span></th>
                                                        <th style="min-width:110px;">Harga <span
                                                                class="text-danger">*</span></th>
                                                        <th style="min-width:110px;">Margin <span
                                                                class="text-danger">*</span></th>
                                                        <th style="min-width:150px;">Kios <span
                                                                class="text-danger">*</span></th>
                                                        <th style="min-width:120px;">Status</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bulkMenuBody"></tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"
                                                name="input_menu_bulk_proses">Simpan Semua</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template id="bulkRowTemplate">
                        <tr>
                            <td><input type="text" class="form-control form-control-sm" name="nama_menu[]"></td>
                            <td><input type="text" class="form-control form-control-sm" name="keterangan[]"></td>
                            <td>
                                <select class="form-select form-select-sm" name="kategori_menu[]">
                                    <option value="" selected hidden>Pilih</option>
                                    <?php
                                    foreach ($sel_kategori as $row2) {
                                        ?>
                                        <option value="<?php echo $row2['id_kategori'] ?>">
                                            <?php echo $row2['kategori_menu'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="harga[]"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="pajak[]"></td>
                            <td>
                                <select class="form-select form-select-sm" name="kios[]">
                                    <option value="" selected hidden>Pilih</option>
                                    <?php
                                    foreach ($result2 as $row2) {
                                        ?>
                                        <option value="<?php echo $row2['nama'] ?>"><?php echo $row2['nama'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" name="status_aktif[]">
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-sm btn-danger btn-hapus-baris"><i
                                        class="bi bi-x-lg"></i></button></td>
                        </tr>
                    </template>

                    <!-- Modal import CSV/Excel -->
                    <div class="modal fade" id="ModalImport" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="ModalImportLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-fullscreen-md-down">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="ModalImportLabel">Import Menu (CSV / Excel)</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="validate/validate_menu_import.php" method="post"
                                        enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label class="form-label">File CSV / XLSX / XLS</label>
                                            <input type="file" class="form-control" name="file_import"
                                                accept=".csv,.xlsx,.xls" required>
                                        </div>
                                        <div class="alert alert-info">
                                            <p class="mb-1"><strong>Format kolom (baris pertama = header):</strong></p>
                                            <code>nama, keterangan, kategori, harga, margin, kios, status</code>
                                            <ul class="mb-0 mt-2 small">
                                                <li>Kolom dicocokkan dari nama header, urutan bebas.</li>
                                                <li><code>kategori</code>: nama kategori atau id-nya.</li>
                                                <li><code>kios</code>: harus sama dengan nama kios yang terdaftar.</li>
                                                <li><code>status</code>: aktif/nonaktif (default aktif).</li>
                                                <li>Menu yang namanya sudah ada akan dilewati.</li>
                                                <li>Foto tidak ikut ter-import, isi lewat Edit.</li>
                                            </ul>
                                            <a href="menu_import_template.php"
                                                class="btn btn-sm btn-outline-secondary mt-2">Download Template
                                                CSV</a>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"
                                                name="input_menu_import_proses">Import</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                }
                ?>

                <?php

                foreach ($result as $row) {
                    ?>
                    <!-- Modal edit -->
                    <div class="modal fade" id="ModalEdit<?php echo $row['id'] ?>" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Makanan Dan Minuman</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="needs-validation" novalidate action="validate/validate_menu_edit.php"
                                        method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                                        <div class="row mt-3">
                                            <div class="col lg-12">
                                                <div class="input-group">
                                                    <input type="file" class="form-control py-9" id="floatingInputGambar"
                                                        placeholder="Masukan Gambar" name="foto">
                                                    <label class="input-group-text" for="floatingInputGambar">Upload Foto
                                                        Menu</label>
                                                    <div class="invalid-feedback">
                                                        Gambar tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="floatingNama"
                                                        placeholder="Masukan Nama" name="nama_menu"
                                                        value="<?php echo $row['nama'] ?>" required>
                                                    <label for="floatingNama">Nama Makanan</label>
                                                    <div class="invalid-feedback">
                                                        Nama tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="floatingKeterangan"
                                                        placeholder="Masukan Keterangan" name="keterangan"
                                                        value="<?php echo $row['keterangan'] ?>">
                                                    <label for="floatingKeterangan">Keterangan</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col lg-4">
                                                <div class="form-floating mt-3">
                                                    <select class="form-select" aria-label="Default select example"
                                                        name="kategori_menu" required>
                                                        <option selected hidden value="">Pilih Jenis Menu</option>
                                                        <?php
                                                        foreach ($sel_kategori as $row2) {
                                                            if ($row['kategori'] == $row2['id_kategori']) {
                                                                echo "<option selected value='" . $row2['id_kategori'] . "'>" . $row2['kategori_menu'] . "</option>";
                                                            } else {
                                                                echo "<option value='" . $row2['id_kategori'] . "'>" . $row2['kategori_menu'] . "</option>";
                                                            }
                                                            ?>

                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <label for="floatingKategori">Kategori Menu</label>
                                                    <div class="invalid-feedback">
                                                        Jenis Menu tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col lg-4">
                                                <div class="form-floating mt-3">
                                                    <input type="number" class="form-control" id="floatingHarga"
                                                        placeholder="Masukan Harga" name="harga"
                                                        value="<?php echo $row['harga'] ?>" required>
                                                    <label for="floatingHarga">Harga</label>
                                                    <div class="invalid-feedback">
                                                        Harga tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col lg-4">
                                                <div class="form-floating mt-3">
                                                    <input type="number" class="form-control" id="floatingStok"
                                                        placeholder="Masukan Stok" name="pajak"
                                                        value="<?php echo $row['pajak'] ?>" required>
                                                    <label for="floatingStok">Margin</label>
                                                    <div class="invalid-feedback">
                                                        Marjin tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <div class="form-floating mt-3">
                                                    <select class="form-select" aria-label="Default select example"
                                                        name="kios" required>
                                                        <option selected hidden value="">Pilih Kios User</option>
                                                        <?php
                                                        foreach ($result2 as $row2) {
                                                            ?>
                                                            <option value="<?php echo $row2['nama'] ?>" <?php echo ($row['nama_toko'] == $row2['nama']) ? 'selected' : ''; ?>>
                                                                <?php echo $row2['nama'] ?>
                                                            </option>
                                                            <?php
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
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        id="status_aktif_edit_<?php echo $row['id']; ?>" name="status_aktif"
                                                        <?php echo ((int) ($row['status'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label"
                                                        for="status_aktif_edit_<?php echo $row['id']; ?>">
                                                        Aktifkan menu
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"
                                                name="input_menu_edit_proses">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal delete -->
                    <div class="modal fade" id="ModalDelete<?php echo $row['id'] ?>" tabindex="-1"
                        aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Delete</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="needs-validation" novalidate action="validate/validate_menu_delete.php"
                                        method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                                        <input type="hidden" name="foto" value="<?php echo $row['foto'] ?>">
                                        <div class="col-lg-12">

                                            <h5>Apakah Anda yakin ingin menghapus menu
                                                <strong><?php echo $row['nama'] ?></strong>?
                                            </h5>
                                            <p>Data yang dihapus tidak dapat dikembalikan.</p>

                                        </div>


                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger" name="input_menu_delete">Hapus
                                                Data</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Modal view -->
                    <div class="modal fade" id="ModalView<?php echo $row['id'] ?>" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Makanan Dan Minuman</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="needs-validation" novalidate action="validate/validate_menu.php"
                                        method="post" enctype="multipart/form-data">
                                        <div class="row mt-3">

                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="floatingNama"
                                                        placeholder="Masukan Nama" name="nama_menu"
                                                        value="<?php echo $row['nama'] ?>" disabled>
                                                    <label for="floatingNama">Nama Makanan</label>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="floatingKeterangan"
                                                        placeholder="Masukan Keterangan" name="keterangan"
                                                        value="<?php echo $row['keterangan'] ?>" disabled>
                                                    <label for="floatingKeterangan">Keterangan</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col lg-4">
                                                <div class="form-floating mt-3">
                                                    <select disabled class="form-select" aria-label="Default select example"
                                                        name="kategori_menu" required>
                                                        <option selected hidden value="">Pilih Jenis Menu</option>
                                                        <?php
                                                        foreach ($sel_kategori as $row2) {
                                                            if ($row['kategori'] == $row2['id_kategori']) {
                                                                echo "<option selected value='" . $row2['kategori'] . "'>" . $row2['kategori_menu'] . "</option>";
                                                            } else {
                                                                echo "<option value='" . $row2['kategori'] . "'>" . $row2['kategori_menu'] . "</option>";
                                                            }
                                                            ?>

                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <label for="floatingKategori">Kategori Menu</label>
                                                    <div class="invalid-feedback">
                                                        Jenis Menu tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col lg-4">
                                                <div class="form-floating mt-3">
                                                    <input type="number" class="form-control" id="floatingHarga"
                                                        placeholder="Masukan Harga" name="harga"
                                                        value="<?php echo $row['harga'] ?>" disabled>
                                                    <label for="floatingHarga">Harga</label>
                                                    <div class="invalid-feedback">
                                                        Harga tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col lg-4">
                                                <div class="form-floating mt-3">
                                                    <input type="number" class="form-control" id="floatingStok" placeholder="Masukan Stok" name="pajak" value="<?php echo $row['pajak'] ?>" disabled>
                                                    <label for="floatingStok">Margin</label>
                                                    <div class="invalid-feedback">
                                                        Margin tidak boleh kosong
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <div class="form-floating mt-3">
                                                    <select disabled class="form-select" aria-label="Default select example"
                                                        name="kios" required>
                                                        <option selected hidden value="">Pilih Kios User</option>
                                                        <?php
                                                        foreach ($result2 as $row2) {
                                                            ?>
                                                            <option value="<?php echo $row2['nama'] ?>" <?php echo ($row['nama_toko'] == $row2['nama']) ? 'selected' : ''; ?>>
                                                                <?php echo $row2['nama'] ?>
                                                            </option>
                                                            <?php
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
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"
                                                name="input_menu_proses">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <?php
                if (empty($result)) {
                    echo "<div class='alert alert-warning'>Data tidak ditemukan</div>";
                } else {
                    ?>
                    <div class="table-responsive-lg-12">
                        <table class="table table-hover" id="table_menu">
                            <thead>
                                <tr>
                                    <?php
                                    if ($_SESSION["level_kantin"] == 1) {
                                        ?>
                                        <th scope="col"><input class="form-check-input" type="checkbox"
                                                id="checkAllMenu" aria-label="Pilih semua"></th>
                                        <?php
                                    } else {
                                    }
                                    ?>
                                    <th scope="col">No</th>
                                    <th scope="col">Foto Menu</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">Keterangan</th>
                                    <th scope="col">Jenis Menu</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Harga</th>
                                    <th scope="col">Nama Toko</th>
                                    <th scope="col">Status</th>
                                    <?php
                                    if ($_SESSION["level_kantin"] == 1) {
                                        ?>
                                        <th scope="col">Aksi</th>
                                        <?php
                                    } else {
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $id_nomor = 1;
                                foreach ($result as $row) {
                                    ?>
                                    <tr>
                                        <?php
                                        if ($_SESSION["level_kantin"] == 1) {
                                            ?>
                                            <td><input class="form-check-input menu-check" type="checkbox" name="ids[]"
                                                    value="<?php echo $row['id'] ?>" form="bulkDeleteForm"></td>
                                            <?php
                                        } else {
                                        }
                                        ?>
                                        <th scope="row"><?php echo $id_nomor++ ?></th>
                                        <td>
                                            <div style="width: 100px;">
                                                <?php if (!empty($row['foto'])) { ?>
                                                    <img src="assets/img/<?php echo $row['foto'] ?>" class="img-thumbnail"
                                                        alt="<?php echo htmlspecialchars($row['nama']); ?>">
                                                <?php } else { ?>
                                                    <span class="badge bg-light text-dark border">Tanpa Foto</span>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td><?php echo $row['nama'] ?></td>
                                        <td><?php echo $row['keterangan'] ?></td>
                                        <td><?php echo ($row['jenis_menu'] == 1) ? "Makanan" : "Minuman" ?></td>
                                        <td><?php echo $row['kategori_menu'] ?></td>
                                        <td><?php echo $row['harga'] + $row['pajak'] ?></td>
                                        <td><?php echo $row['nama_toko'] ?></td>
                                        <td>
                                            <?php if ((int) ($row['status'] ?? 0) === 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php } ?>
                                        </td>
                                        <?php
                                        if ($_SESSION["level_kantin"] == 1) {
                                            ?>
                                            <td>
                                                <div class="d-flex">
                                                    <button class="btn btn-info btn-sm me-2" data-bs-toggle="modal"
                                                        data-bs-target="#ModalView<?php echo $row['id'] ?>"> <i
                                                            class="bi bi-eye-fill"></i></button>
                                                    <button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal"
                                                        data-bs-target="#ModalEdit<?php echo $row['id'] ?>"> <i
                                                            class="bi bi-pencil-fill"></i></button>
                                                    <form action="validate/validate_menu_toggle_status.php" method="post" class="me-2">
                                                        <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                                                        <input type="hidden" name="status" value="<?php echo (int) ($row['status'] ?? 0); ?>">
                                                        <button type="submit" name="toggle_menu_status"
                                                            class="btn btn-<?php echo ((int) ($row['status'] ?? 0) === 1) ? 'secondary' : 'success'; ?> btn-sm"
                                                            onclick="return confirm('Ubah status menu ini?')">
                                                            <?php echo ((int) ($row['status'] ?? 0) === 1) ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#ModalDelete<?php echo $row['id'] ?>"> <i
                                                            class="bi bi-trash-fill"></i></button>
                                                </div>

                                            </td>
                                            <?php
                                        } else {
                                        }
                                        ?>

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
    let table = new DataTable('#table_menu');
</script>

<script>
    (() => {
        const form = document.getElementById('bulkDeleteForm');
        if (!form) return;

        const btn = document.getElementById('btnBulkDelete');
        const countEl = document.getElementById('bulkDeleteCount');
        const checkAll = document.getElementById('checkAllMenu');
        const boxes = () => Array.from(document.querySelectorAll('.menu-check'));

        const refresh = () => {
            const all = boxes();
            const checked = all.filter((c) => c.checked).length;
            if (countEl) countEl.textContent = checked;
            if (btn) btn.disabled = checked === 0;
            if (checkAll) {
                checkAll.checked = all.length > 0 && checked === all.length;
                checkAll.indeterminate = checked > 0 && checked < all.length;
            }
        };

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('menu-check')) refresh();
        });

        if (checkAll) {
            checkAll.addEventListener('change', () => {
                boxes().forEach((c) => { c.checked = checkAll.checked; });
                refresh();
            });
        }

        refresh();
    })()
</script>

<script>
    (() => {
        const body = document.getElementById('bulkMenuBody');
        const tpl = document.getElementById('bulkRowTemplate');
        const btnAdd = document.getElementById('btnTambahBaris');
        const modal = document.getElementById('ModalBulkTambah');
        const globalKios = document.getElementById('bulkKiosGlobal');
        if (!body || !tpl || !btnAdd || !modal) return;

        const setRowKios = (row, value) => {
            const sel = row.querySelector('select[name="kios[]"]');
            if (sel && value) sel.value = value;
        };

        const addRow = () => {
            body.appendChild(tpl.content.cloneNode(true));
            if (globalKios && globalKios.value) setRowKios(body.lastElementChild, globalKios.value);
        };

        const ensureOne = () => {
            if (body.children.length === 0) addRow();
        };

        btnAdd.addEventListener('click', addRow);

        if (globalKios) {
            globalKios.addEventListener('change', () => {
                if (!globalKios.value) return;
                body.querySelectorAll('select[name="kios[]"]').forEach((sel) => {
                    sel.value = globalKios.value;
                });
            });
        }

        body.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-hapus-baris');
            if (!btn) return;
            btn.closest('tr').remove();
            ensureOne();
        });

        modal.addEventListener('shown.bs.modal', ensureOne);
    })()
</script>

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
