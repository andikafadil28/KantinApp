<?php
include "Database/connect.php";
?>
<div class="container-fluid bg-light min-vh-100 p-0">
    <header class="p-5 text-center text-white" style="background-image: url('URL_GAMBAR_MAKANAN_KEREN_DI_SINI'); background-size: cover; background-position: center; background-color: #ff6347;">
        <div style="background-color: rgba(0, 0, 0, 0.4); padding: 20px; border-radius: 10px;">
            <i class="bi bi-fork-knife display-4 mb-3"></i>
            <h1 class="display-3 fw-bold">Selamat Datang di Sakina Kantin</h1>
            <p class="lead mb-4">Pesan makanan favorit Anda dengan mudah dan cepat!</p>
            <a href="order" class="btn btn-warning btn-lg shadow-sm text-dark fw-bold">
                <i class="bi bi-basket me-2"></i> Mulai Order Sekarang!
            </a>
        </div>
    </header>
    <?php
    include "Database/Query/menu_telaris.php";
    ?>
    <div class="container my-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        📊 Menu Terlaris Hari Ini
                    </h5>
                    <span class="badge bg-warning text-dark">
                        Update: Hari ini
                    </span>
                </div>

                <canvas id="menuTerlarisChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <h2 class="text-center mb-5 fw-bold text-dark">Mengapa Memilih Sakina Kantin?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-3">
                    <div class="card-body">
                        <i class="bi bi-clock-history display-4 text-primary mb-3"></i>
                        <h5 class="card-title fw-bold">Pesan Cepat</h5>
                        <p class="card-text">Tidak perlu antri, pesan dalam hitungan detik.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-3">
                    <div class="card-body">
                        <i class="bi bi-geo-alt display-4 text-success mb-3"></i>
                        <h5 class="card-title fw-bold">Menu Lengkap</h5>
                        <p class="card-text">Pilihan makanan dan minuman beragam.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-3">
                    <div class="card-body">
                        <i class="bi bi-tags display-4 text-danger mb-3"></i>
                        <h5 class="card-title fw-bold">Promo Spesial</h5>
                        <p class="card-text">Dapatkan penawaran terbaik setiap hari.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <footer class="text-center py-3 bg-dark text-white">
        &copy; 2025 Sakina Kantin. Semua Hak Dilindungi.
    </footer>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels = <?= json_encode($menu) ?>;
    const dataValues = <?= json_encode($total) ?>;

    if (labels.length === 0) {
        document.getElementById('menuTerlarisChart').outerHTML = `
    <div class="text-center py-5 text-muted">
      <i class="bi bi-graph-up fs-1 mb-3"></i>
      <p>Belum ada penjualan hari ini</p>
    </div>
  `;
    } else {
        const ctx = document.getElementById('menuTerlarisChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: labels.map(() => [
                        '#ff7a18', '#ffc107', '#4caf50', '#17a2b8', '#dc3545'
                    ][Math.floor(Math.random() * 5)]),
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.raw + ' porsi'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>