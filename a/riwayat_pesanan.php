<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM riwayat_pesanan WHERE user_id = '$user_id' ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<!-- <body class="bg-#E8DCCB dark:bg-gray-900 dark:text-white transition duration-300 min-h-screen"> -->
<body class="bg-[#E8DCCB] dark:bg-gray-900 dark:text-white transition duration-300 min-h-screen">
    <div class="max-w-4xl mx-auto mt-10 p-6 bg-[#F4EDE4] rounded shadow">
        <h1 class="text-2xl text-center text-purple-700 font-bold mb-4">Riwayat Pesanan Anda</h1>
        <?php if (mysqli_num_rows($result) > 0) : ?>
            <div class="space-y-4">
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <div class="border bg-white rounded p-4">
                        <div class="flex justify-between font-semibold">
                            <span>ID Pesanan: <?= $row['id'] ?></span>
                            <span class="text-sm text-gray-500"><?= $row['tanggal'] ?></span>
                        </div>
                        <div>Nama Pemesan: <?= htmlspecialchars($row['nama']) ?></div>
                        <div>Alamat: <?= htmlspecialchars($row['alamat']) ?></div>
                        <div>Jasa Kirim: <?= $row['jasa_kirim'] ?></div>
                        <div class="mt-2">Total: <span class="font-bold text-purple-700">Rp<?= number_format($row['total'], 0, ',', '.') ?></span></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p class="text-center text-gray-500">Belum ada pesanan.</p>
        <?php endif; ?>
    </div>
    <div class="flex flex-col items-center mt-8 space-y-4">
        <a href="index.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Beranda</a>
        <a href="logout.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Logout</a>
    </div>
</body>
</html>