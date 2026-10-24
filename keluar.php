<?php
require 'function.php';
require 'cek.php';

// Riwayat barang keluar (join nama barang)
$riwayat = mysqli_query($conn, "
    SELECT k.idkeluar, k.penerima, k.qty, k.tanggal, s.namabarang, s.idbarang
    FROM keluar k
    JOIN stock s ON s.idbarang = k.idbarang
    ORDER BY k.tanggal DESC, k.idkeluar DESC
");

$daftarBarang = get_all_barang($conn);

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Riwayat barang keluar" />
        <meta name="author" content="" />
        <title>Barang Keluar</title>
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
                        <h1 class="mt-4">Barang Keluar</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Barang Keluar</li>
                        </ol>

                        <?php if ($status === 'stokk') : ?>
                            <div class="alert alert-danger" role="alert">Jumlah keluar melebihi stok yang tersedia. Transaksi dibatalkan.</div>
                        <?php elseif ($status === 'ok') : ?>
                            <div class="alert alert-success" role="alert">Transaksi barang keluar berhasil disimpan.</div>
                        <?php endif; ?>

                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-table me-1"></i> Riwayat Barang Keluar</span>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalKeluar">
                                    <i class="fas fa-plus me-1"></i>Tambah Barang Keluar
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Penerima</th>
                                            <th>Qty</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php while ($row = mysqli_fetch_assoc($riwayat)) : ?>
                                            <tr>
                                                <td><?php echo e($no); ?></td>
                                                <td><?php echo e($row['namabarang']); ?></td>
                                                <td><?php echo e($row['penerima']); ?></td>
                                                <td><?php echo e($row['qty']); ?></td>
                                                <td><?php echo e(date('d M Y H:i', strtotime($row['tanggal']))); ?></td>
                                            </tr>
                                            <?php $no++; ?>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="small text-muted">StockBarang &copy; <?php echo date('Y'); ?></div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Modal Tambah Barang Keluar -->
        <div class="modal fade" id="modalKeluar" tabindex="-1" aria-labelledby="modalKeluarLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="keluar.php">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h4 class="modal-title" id="modalKeluarLabel">Tambah Barang Keluar</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Pilih Barang</label>
                                <select name="barangnya" class="form-select" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php foreach ($daftarBarang as $b) : ?>
                                        <option value="<?php echo e($b['idbarang']); ?>">
                                            <?php echo e($b['namabarang']); ?> (stok: <?php echo e($b['stock']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Penerima</label>
                                <input type="text" name="penerima" placeholder="Contoh: Divisi Operasional" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah Barang Keluar</label>
                                <input type="number" name="qty" placeholder="Jumlah Barang Keluar" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" name="barangkeluar">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
