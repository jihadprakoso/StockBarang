<?php
require 'function.php';
require 'cek.php';

// Ringkasan data
$barang   = get_all_barang($conn);
$totalStok = 0;
foreach ($barang as $b) {
    $totalStok += (int) $b['stock'];
}

$totalBarang = count($barang);
$totalMasuk  = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM masuk"))['n'];
$totalKeluar = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM keluar"))['n'];

// Data grafik: 6 barang dengan stok terbanyak
$chartRows = mysqli_query($conn, "SELECT namabarang, stock FROM stock ORDER BY stock DESC LIMIT 6");
$chartLabel = [];
$chartData  = [];
while ($c = mysqli_fetch_assoc($chartRows)) {
    $chartLabel[] = $c['namabarang'];
    $chartData[]  = (int) $c['stock'];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Aplikasi manajemen stok barang" />
        <meta name="author" content="" />
        <title>Stock Barang</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand ps-3" href="index.php">Gudang D'ArtBox</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Stock Barang
                            </a>
                            <a class="nav-link" href="masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-arrow-down"></i></div>
                                Barang Masuk
                            </a>
                            <a class="nav-link" href="keluar.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-arrow-up"></i></div>
                                Barang Keluar
                            </a>
                            <a class="nav-link" href="logout.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-right-from-bracket"></i></div>
                                Logout
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?php echo e($_SESSION['user']['email'] ?? ''); ?>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Stock Barang</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>

                        <!-- Kartu ringkasan -->
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">
                                        <div class="small">Jenis Barang</div>
                                        <div class="fs-3 fw-bold"><?php echo e($totalBarang); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">
                                        <div class="small">Total Stok</div>
                                        <div class="fs-3 fw-bold"><?php echo e($totalStok); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-info text-white mb-4">
                                    <div class="card-body">
                                        <div class="small">Transaksi Masuk</div>
                                        <div class="fs-3 fw-bold"><?php echo e($totalMasuk); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">
                                        <div class="small">Transaksi Keluar</div>
                                        <div class="fs-3 fw-bold"><?php echo e($totalKeluar); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Stok Terbanyak
                            </div>
                            <div class="card-body"><canvas id="stockChart" width="100%" height="35"></canvas></div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-table me-1"></i> Daftar Barang</span>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    <i class="fas fa-plus me-1"></i>Tambah Barang
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Deskripsi</th>
                                            <th>Stock</th>
                                            <th style="width: 130px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($barang as $row) : ?>
                                            <tr>
                                                <td><?php echo e($no); ?></td>
                                                <td><?php echo e($row['namabarang']); ?></td>
                                                <td><?php echo e($row['deskripsi']); ?></td>
                                                <td><?php echo e($row['stock']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                            data-id="<?php echo e($row['idbarang']); ?>"
                                                            data-nama="<?php echo e($row['namabarang']); ?>"
                                                            data-deskripsi="<?php echo e($row['deskripsi']); ?>"
                                                            data-stock="<?php echo e($row['stock']); ?>">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                            data-id="<?php echo e($row['idbarang']); ?>"
                                                            data-nama="<?php echo e($row['namabarang']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php $no++; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">StockBarang &copy; <?php echo date('Y'); ?></div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="index.php">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h4 class="modal-title" id="modalTambahLabel">Tambah Barang</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="deskripsi" placeholder="Deskripsi Barang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="number" name="stock" placeholder="Stock" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" name="addnewbarang">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="index.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="idbarang" id="edit-id">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modalEditLabel">Edit Barang</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <input type="text" name="namabarang" id="edit-nama" placeholder="Nama Barang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="deskripsi" id="edit-deskripsi" placeholder="Deskripsi Barang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="number" name="stock" id="edit-stock" placeholder="Stock" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning" name="editbarang">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="index.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="idbarang" id="hapus-id">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modalHapusLabel">Hapus Barang</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">Yakin ingin menghapus <strong id="hapus-nama"></strong>? Riwayat barang masuk/keluar terkait juga akan terhapus.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger" name="hapusbarang">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
        <script>
            // Isi form edit & hapus dari data tombol
            document.getElementById('modalEdit').addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                document.getElementById('edit-id').value = btn.getAttribute('data-id');
                document.getElementById('edit-nama').value = btn.getAttribute('data-nama');
                document.getElementById('edit-deskripsi').value = btn.getAttribute('data-deskripsi');
                document.getElementById('edit-stock').value = btn.getAttribute('data-stock');
            });
            document.getElementById('modalHapus').addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                document.getElementById('hapus-id').value = btn.getAttribute('data-id');
                document.getElementById('hapus-nama').textContent = btn.getAttribute('data-nama');
            });

            // Grafik stok
            new Chart(document.getElementById('stockChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabel, JSON_UNESCAPED_UNICODE); ?>,
                    datasets: [{
                        label: 'Jumlah Stok',
                        data: <?php echo json_encode($chartData); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.6)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        </script>
    </body>
</html>
