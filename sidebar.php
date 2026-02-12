<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="home">
        <div class="sidebar-brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <div class="sidebar-brand-text mx-2">Sakina Kantin</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="home">
            <i class="fas fa-fw fa-home"></i> <span>Home</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOrder"
            aria-expanded="true" aria-controls="collapseOrder">
            <i class="bi bi-cash-coin"></i> <span>Transaksi & Menu</span>
        </a>
        <div id="collapseOrder" class="collapse" aria-labelledby="headingOrder" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="menu">Daftar Menu</a>
                <a class="collapse-item" href="order">Buat Order Baru</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport"
            aria-expanded="true" aria-controls="collapseReport">
            <i class="bi bi-graph-up"></i> <span>Laporan Penjualan</span>
        </a>
        <div id="collapseReport" class="collapse" aria-labelledby="headingReport" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Detail Pendapatan:</h6>
                <a class="collapse-item" href="laporan">Pendapatan Detail</a>
                <a class="collapse-item" href="laporanrs">Pendapatan Kantin Detail</a>
                <a class="collapse-item" href="laporantoko">Pendapatan Toko Detail</a>
                <h6 class="collapse-header mt-2">Rekapitulasi:</h6>
                <a class="collapse-item" href="history">Rekap Toko</a>
                <a class="collapse-item" href="rekaprs">Rekap RS</a>
                <a class="collapse-item" href="rekapmenurs">Rekap Kantin</a>
                <a class="collapse-item" href="rekapkeuangan">Rekap Keuangan</a>
            </div>
        </div>
    </li>

    <?php
    // Bagian PHP untuk Level 1 (Admin)
    if ($hasil['level'] == 1) {
    ?>
        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettings"
                aria-expanded="true" aria-controls="collapseSettings">
                <i class="bi bi-gear-fill"></i> <span>Pengaturan Sistem</span>
            </a>
            <div id="collapseSettings" class="collapse" aria-labelledby="headingSettings" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Manajemen Data:</h6>
                    <a class="collapse-item" href="user">Manajemen User</a>
                    <a class="collapse-item" href="kios">Manajemen Toko</a>
                </div>
            </div>
        </li>
    <?php
    }
    ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>