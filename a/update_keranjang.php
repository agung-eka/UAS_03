<?php
session_start();

$id = $_GET['id'] ?? null;
$aksi = $_GET['aksi'] ?? null;

if ($id && $aksi) {
    if (!isset($_SESSION['keranjang'][$id])) {
        $_SESSION['keranjang'][$id] = 1;
    }

    if ($aksi === 'tambah') {
        $_SESSION['keranjang'][$id]++;
    } elseif ($aksi === 'kurang') {
        $_SESSION['keranjang'][$id]--;
        if ($_SESSION['keranjang'][$id] <= 0) {
            unset($_SESSION['keranjang'][$id]);
        }
    }
}

header('Location: keranjang.php');
exit;
