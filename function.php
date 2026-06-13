<?php
session_start();

// Aktifkan error reporting untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Membuat koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stockbarang");

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Menambah barang baru
if (isset($_POST['addnewbarang'])) {
    $namabarang = $_POST['namabarang'];
    $deskripsi  = $_POST['deskripsi'];
    $stock      = $_POST['stock'];

    $addtotable = mysqli_query($conn, "insert into stock (namabarang, deskripsi, stock) values('$namabarang','$deskripsi','$stock')");

    if ($addtotable) {
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    }
};

//Menambah barang masuk
if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];  // idbarang dari dropdown
    $penerima  = $_POST['penerima'];   // disimpan ke kolom keterangan
    $qty       = $_POST['qty'];

    // 1. Insert ke tabel masuk (log transaksi)
    $addtomasuk = mysqli_query($conn, "INSERT INTO masuk (idbarang, keterangan, qty) VALUES ('$barangnya', '$penerima', '$qty')");

    if($addtomasuk){
        // 2. Update stock di tabel stock (tambah qty ke stock utama)
        $updatestock = mysqli_query($conn, "UPDATE stock SET stock = stock + '$qty' WHERE idbarang = '$barangnya'");

        if($updatestock){
            header('location:masuk.php');
            exit();
        } else {
            die('Gagal update stock: ' . mysqli_error($conn));
        }
    } else {
        die('Gagal insert masuk: ' . mysqli_error($conn));
    }
}

// Barang Keluar
if(isset($_POST['barangkeluar'])){
    $barangnya = $_POST['barangnya'];  // idbarang dari dropdown
    $penerima  = $_POST['penerima'];
    $qty       = $_POST['qty'];

    // 1. Insert ke tabel keluar (log transaksi)
    $addtokeluar = mysqli_query($conn, "INSERT INTO keluar (idbarang, penerima, qty) VALUES ('$barangnya', '$penerima', '$qty')");

    if($addtokeluar){
        // 2. Kurangi stock di tabel stock
        $updatestock = mysqli_query($conn, "UPDATE stock SET stock = stock - '$qty' WHERE idbarang = '$barangnya'");

        if($updatestock){
            header('location:keluar.php');
            exit();
        } else {
            die('Gagal update stock: ' . mysqli_error($conn));
        }
    } else {
        die('Gagal insert keluar: ' . mysqli_error($conn));
    }
}
?>