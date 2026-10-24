<?php
// ============================================================
// StockBarang - Fungsi inti: koneksi, helper keamanan, dan CRUD
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error dicatat ke log, tidak ditampilkan ke pengguna
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ------------------------------------------------------------
// Konfigurasi database
// ------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stockbarang_local');

// mysqli melempar exception agar setiap kegagalan query dapat ditangani
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Koneksi database gagal: ' . $e->getMessage());
    die('Koneksi database gagal. Pastikan MySQL berjalan dan database telah diimpor.');
}

// ------------------------------------------------------------
// Helper keamanan
// ------------------------------------------------------------

/** Escape output agar aman dari XSS. */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Ambil (atau buat) CSRF token untuk sesi ini. */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Cetak hidden input CSRF untuk dipakai di dalam <form>. */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/** Tolak request POST yang tidak membawa CSRF token yang sah. */
function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Permintaan tidak valid: CSRF token tidak cocok.');
    }
}

/** Redirect lalu hentikan eksekusi. */
function redirect($url)
{
    header('location: ' . $url);
    exit();
}

/** Ambil seluruh data barang. */
function get_all_barang($conn)
{
    $rows = [];
    $result = mysqli_query($conn, "SELECT * FROM stock ORDER BY idbarang ASC");
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// ------------------------------------------------------------
// Aksi: Barang
// ------------------------------------------------------------

// Tambah barang baru
if (isset($_POST['addnewbarang'])) {
    verify_csrf();

    $namabarang = trim($_POST['namabarang'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $stock      = (int) ($_POST['stock'] ?? 0);

    if ($namabarang === '') {
        redirect('index.php');
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO stock (namabarang, deskripsi, stock) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssi', $namabarang, $deskripsi, $stock);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    redirect('index.php');
}

// Ubah data barang
if (isset($_POST['editbarang'])) {
    verify_csrf();

    $idbarang   = (int) ($_POST['idbarang'] ?? 0);
    $namabarang = trim($_POST['namabarang'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $stock      = (int) ($_POST['stock'] ?? 0);

    if ($idbarang > 0 && $namabarang !== '') {
        $stmt = mysqli_prepare($conn, "UPDATE stock SET namabarang = ?, deskripsi = ?, stock = ? WHERE idbarang = ?");
        mysqli_stmt_bind_param($stmt, 'ssii', $namabarang, $deskripsi, $stock, $idbarang);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    redirect('index.php');
}

// Hapus barang
if (isset($_POST['hapusbarang'])) {
    verify_csrf();

    $idbarang = (int) ($_POST['idbarang'] ?? 0);
    if ($idbarang > 0) {
        // Riwayat masuk/keluar ikut terhapus via ON DELETE CASCADE
        $stmt = mysqli_prepare($conn, "DELETE FROM stock WHERE idbarang = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idbarang);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    redirect('index.php');
}

// ------------------------------------------------------------
// Aksi: Barang Masuk
// ------------------------------------------------------------
if (isset($_POST['barangmasuk'])) {
    verify_csrf();

    $idbarang   = (int) ($_POST['barangnya'] ?? 0);
    $keterangan = trim($_POST['penerima'] ?? '');
    $qty        = (int) ($_POST['qty'] ?? 0);

    if ($idbarang > 0 && $qty > 0) {
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO masuk (idbarang, keterangan, qty) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isi', $idbarang, $keterangan, $qty);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "UPDATE stock SET stock = stock + ? WHERE idbarang = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $qty, $idbarang);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conn);
            error_log('Gagal menambah barang masuk: ' . $e->getMessage());
        }
    }

    redirect('masuk.php');
}

// ------------------------------------------------------------
// Aksi: Barang Keluar
// ------------------------------------------------------------
if (isset($_POST['barangkeluar'])) {
    verify_csrf();

    $idbarang = (int) ($_POST['barangnya'] ?? 0);
    $penerima = trim($_POST['penerima'] ?? '');
    $qty      = (int) ($_POST['qty'] ?? 0);

    // Pastikan stock mencukupi sebelum dikurangi
    $stockTersedia = 0;
    if ($idbarang > 0) {
        $stmt = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idbarang);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        $stockTersedia = $row ? (int) $row['stock'] : 0;
    }

    if ($idbarang > 0 && $qty > 0 && $qty <= $stockTersedia) {
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO keluar (idbarang, penerima, qty) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isi', $idbarang, $penerima, $qty);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "UPDATE stock SET stock = stock - ? WHERE idbarang = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $qty, $idbarang);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conn);
            error_log('Gagal menambah barang keluar: ' . $e->getMessage());
        }
    }

    redirect('keluar.php?status=' . ($qty > $stockTersedia ? 'stokk' : 'ok'));
}
