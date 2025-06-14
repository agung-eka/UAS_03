<?php
session_start();
include 'koneksi.php';

$produk = mysqli_query($conn, "SELECT * FROM produk");
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Linen Legacy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&display=swap" rel="stylesheet">

  <script>
    tailwind.config = {
      darkMode: 'class',
    };
  </script>

  <style>
    html {
      scroll-behavior: smooth;
    }
    .produk-item {
      transition: transform 0.2s ease-in-out;
      cursor: pointer;
    }
    .produk-item:active {
      transform: scale(1.08);
    }
    .brand-font {
      font-family: 'Cinzel', serif;
    }
  </style>
</head>
<body class="bg-[#F4EDE4] dark:bg-gray-900 dark:text-white transition duration-300">

  <!-- NAVBAR -->
  <nav class="bg-white dark:bg-gray-800 shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
      <div class="text-xl font-bold text-purple-700 brand-font">Linen Legacy</div>
      <div class="hidden md:flex space-x-6 text-sm md:text-base font-medium mx-auto">
        <a href="#home" class="hover:text-purple-600 transition">Home</a>
        <a href="#produk" class="hover:text-purple-600 transition">Produk</a>
        <a href="#about" class="hover:text-purple-600 transition">About</a>
        <a href="#kontak" class="hover:text-purple-600 transition">Kontak</a>
      </div>
      <div class="flex space-x-4 items-center">
        <a href="keranjang.php" class="relative" id="keranjangIcon">🛒
          <?php if (!empty($_SESSION['keranjang'])): ?>
            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1"><?= count($_SESSION['keranjang']); ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= isset($_SESSION['user_id']) ? 'logout.php' : 'login.php' ?>" class="hover:text-purple-600 transition">
          <?= isset($_SESSION['user_id']) ? 'Logout' : 'Login' ?>
        </a>
        <button onclick="document.documentElement.classList.toggle('dark')">🌃</button>
      </div>
    </div>
  </nav>

  <!-- BANNER -->
  <div id="home" class="relative w-full h-[500px] overflow-hidden">
    <img src="atas.jpg" class="w-full h-600 object-cover opacity-70" alt="banner">
  </div>

  <!-- PRODUK -->
  <section id="produk" class="max-w-7xl mx-auto px-4 py-12">
    <h2 class="text-3xl font-semibold text-center mb-8" data-aos="fade-up">Produk Terbaru</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
      <?php while ($row = mysqli_fetch_assoc($produk)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-3 flex flex-col items-center text-center hover:shadow-xl produk-item" data-aos="zoom-in">
          <img src="img/<?= $row['gambar'] ?>" alt="<?= $row['nama'] ?>" class="h-40 w-full object-cover mb-3 rounded">
          <h3 class="font-medium text-base"><?= $row['nama'] ?></h3>
          <p class="text-purple-600 font-semibold mb-2 text-sm">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
          <button onclick="terbangKeKeranjang(this, <?= $row['id'] ?>)" class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 transition text-sm">+ Keranjang</button>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about" class="bg-gradient-to-b from-purple-50 to-white dark:from-gray-800 dark:to-gray-900 py-20 px-6">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-12" data-aos="fade-up">
      <div class="flex-1">
        <img src="img/about.jpg" class="rounded-2xl shadow-xl w-full" alt="Tentang Linen Legacy" />
      </div>
      <div class="flex-1 text-center md:text-left">
        <h2 class="text-4xl font-extrabold text-purple-700 mb-6">Cerita di Balik Linen Legacy</h2>
        <p class="text-lg leading-relaxed text-gray-700 dark:text-gray-300 mb-4">
          Sejak <span class="font-semibold text-purple-600">2014</span>, <strong>Linen Legacy</strong> hadir sebagai pionir dalam dunia fashion modern yang mengutamakan <em>elegansi, kenyamanan, dan kualitas tinggi</em>.
        </p>
        <p class="text-lg leading-relaxed text-gray-700 dark:text-gray-300 mb-6">
          Dari keseharian santai hingga acara istimewa, kami siap menemani Anda tampil percaya diri.
        </p>
        <a href="#produk" class="inline-block bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition shadow-md text-sm">Lihat Koleksi Kami</a>
      </div>
    </div>
  </section>

  <!-- KONTAK -->
  <section id="kontak" class="py-16 px-6 bg-[#F4EDE4] dark:bg-gray-900">
    <div class="max-w-3xl mx-auto text-center space-y-6" data-aos="fade-up">
      <h2 class="text-3xl font-bold text-purple-700">Hubungi Kami</h2>
      <div class="flex items-center justify-center gap-4">
        <img src="img/email.jfif" class="w-7 h-7 rounded-full" alt="email icon">
        <a href="mailto:linenlegacy@gmail.com" class="text-lg text-purple-600 hover:underline">linenlegacy@gmail.com</a>
      </div>
      <div class="flex items-center justify-center gap-4">
        <img src="img/telepon.jfif" class="w-7 h-7 rounded-full" alt="phone icon">
        <a href="tel:+6281234567890" class="text-lg text-purple-600 hover:underline">+62 812 3456 7890</a>
      </div>
      <div class="flex items-center justify-center gap-4">
        <img src="img/maps.jfif" class="w-7 h-7 rounded-full" alt="map icon">
        <span class="text-lg text-gray-800 dark:text-gray-300">Jl. Cakrawala No. 123, Jakarta</span>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-200 dark:bg-gray-900 py-4 text-center text-sm text-gray-600 dark:text-gray-300">
    © <?= date('Y') ?> Linen Legacy. All rights reserved.
  </footer>

  <!-- JS -->
  <script>
    function terbangKeKeranjang(btn, id) {
      const card = btn.closest('.produk-item');
      const img = card.querySelector('img');
      const keranjang = document.getElementById('keranjangIcon');
      const clone = img.cloneNode();
      const rect = img.getBoundingClientRect();
      const targetRect = keranjang.getBoundingClientRect();

      clone.style.position = 'fixed';
      clone.style.left = rect.left + 'px';
      clone.style.top = rect.top + 'px';
      clone.style.width = rect.width + 'px';
      clone.style.height = rect.height + 'px';
      clone.style.transition = 'all 0.8s ease';
      clone.style.zIndex = 9999;
      clone.style.borderRadius = '8px';
      document.body.appendChild(clone);

      setTimeout(() => {
        clone.style.left = targetRect.left + 'px';
        clone.style.top = targetRect.top + 'px';
        clone.style.width = '20px';
        clone.style.height = '20px';
        clone.style.opacity = 0;
      }, 10);

      setTimeout(() => clone.remove(), 900);

      fetch('tambah_keranjang.php?id=' + id)
        .then(res => res.json())
        .then(data => {
          if (data.success) updateJumlahKeranjang(data.total_items);
        });
    }

    function updateJumlahKeranjang(total) {
      const badge = document.querySelector('#keranjangIcon span');
      if (badge) {
        badge.innerText = total;
      } else {
        const span = document.createElement('span');
        span.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1';
        span.innerText = total;
        document.getElementById('keranjangIcon').appendChild(span);
      }
    }

    AOS.init({ duration: 1000 });
  </script>

</body>
</html>
