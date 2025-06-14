<?php
// tambah_keranjang.php
session_start();
$id = $_GET['id'];
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
if (isset($_SESSION['keranjang'][$id])) {
    $_SESSION['keranjang'][$id]++;
} else {
    $_SESSION['keranjang'][$id] = 1;
}
echo json_encode([
    'success' => true,
    'total_items' => array_sum($_SESSION['keranjang'])
]);
?>