<?php
if (empty($_SESSION['menu_pending_duplicates'])) {
    echo "<script>window.location.href='menu';</script>";
    exit();
}

$pending = $_SESSION['menu_pending_duplicates'];
$summary = isset($_SESSION['menu_pending_summary']) ? $_SESSION['menu_pending_summary'] : [
    'inserted' => 0,
    'skipped' => count($pending),
    'errors' => [],
    'source' => '',
];
$errors = isset($summary['errors']) && is_array($summary['errors']) ? $summary['errors'] : [];
$total_duplikat = count($pending);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-exclamation-triangle"></i>
            Menu Duplikat
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <strong><?php echo (int) $summary['inserted']; ?></strong> menu berhasil ditambahkan.
                <strong><?php echo $total_duplikat; ?></strong> menu dilewati karena nama + kios sudah ada.
                <?php if (count($errors) > 0) { ?>
                    <br>
                    <strong><?php echo count($errors); ?></strong> baris gagal:
                    <?php echo htmlspecialchars(implode(', ', array_slice($errors, 0, 5))); ?>
                    <?php echo (count($errors) > 5) ? ', ...' : ''; ?>
                <?php } ?>
            </div>

            <p class="mb-2">Menu di bawah ini punya nama yang sama di kios yang sama dengan data yang sudah ada.
                Pilih <strong>Tetap Tambahkan</strong> untuk menyimpan duplikat, atau <strong>Lewati</strong> untuk
                membuangnya.</p>

            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Keterangan</th>
                            <th scope="col">Kios</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        foreach ($pending as $row) {
                            ?>
                            <tr>
                                <td><?php echo $nomor++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                <td><?php echo htmlspecialchars($row['kios']); ?></td>
                                <td><?php echo htmlspecialchars($row['harga']); ?></td>
                                <td><?php echo htmlspecialchars($row['pajak']); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-3">
                <form action="validate/validate_menu_resolve_duplicates.php" method="post">
                    <input type="hidden" name="action" value="force">
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Tetap tambahkan <?php echo $total_duplikat; ?> menu duplikat?')">
                        <i class="bi bi-plus-circle"></i> Tetap Tambahkan Semua
                    </button>
                </form>
                <form action="validate/validate_menu_resolve_duplicates.php" method="post">
                    <input type="hidden" name="action" value="skip">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Lewati Semua
                    </button>
                </form>
                <a href="menu" class="btn btn-outline-secondary">Kembali ke Menu</a>
            </div>
        </div>
    </div>
</div>
