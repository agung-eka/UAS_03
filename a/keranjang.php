<?php
// keranjang.php
session_start();
include 'koneksi.php';

$keranjang = $_SESSION['keranjang'] ?? [];
$produk = [];
$total = 0;

if (!empty($keranjang)) {
    $ids = implode(",", array_map('intval', array_keys($keranjang)));
    $query = mysqli_query($conn, "SELECT * FROM produk WHERE id IN ($ids)");

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $row['jumlah'] = $keranjang[$row['id']];
            $row['subtotal'] = $row['jumlah'] * $row['harga'];
            $total += $row['subtotal'];
            $produk[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Keranjang Belanja</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&display=swap" rel="stylesheet">
  <style>
    .brand-font {
      font-family: 'Cinzel', serif;
    }
  </style>
</head>
<body class="bg-[#F4EDE4] dark:bg-gray-900 dark:text-white transition duration-300 min-h-screen">

  <div class="max-w-4xl mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-center mb-8 brand-font text-purple-700">Keranjang Belanja</h2>

    <?php if (empty($produk)): ?>
      <p class="text-center text-gray-600 dark:text-gray-400 text-lg">Keranjang Anda masih kosong 😢</p>
      <div class="mt-6 text-center">
        <a href="index.php" class="inline-block bg-purple-600 text-white px-5 py-2 rounded hover:bg-purple-700 transition text-sm">
          🛒 Kembali Belanja
        </a>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($produk as $item): ?>
          <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <div class="flex items-center space-x-4">
              <img src="img/<?= $item['gambar'] ?>" class="w-16 h-16 rounded object-cover">
              <div>
                <h3 class="font-semibold"><?= $item['nama'] ?></h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                <div class="flex items-center mt-1 space-x-2">
                  <a href="update_keranjang.php?id=<?= $item['id'] ?>&aksi=kurang" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">−</a>
                  <span class="px-2"><?= $item['jumlah'] ?></span>
                  <a href="update_keranjang.php?id=<?= $item['id'] ?>&aksi=tambah" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">+</a>
                </div>
              </div>
            </div>
            <p class="font-bold text-purple-600">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-10 flex flex-col sm:flex-row justify-between items-center">
        <p class="text-xl font-bold mb-4 sm:mb-0 text-gray-800 dark:text-white">Total: Rp<?= number_format($total, 0, ',', '.') ?></p>
        <div class="flex gap-4">
          <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 transition text-sm">← Kembali Belanja</a>
          <a href="checkout.php" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 transition text-sm">Checkout</a>
        </div>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
