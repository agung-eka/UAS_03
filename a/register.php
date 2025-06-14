<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn, "SELECT * FROM user WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar.";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO user (nama, email, password) VALUES ('$nama', '$email', '$password')");
        if ($insert) {
            header("Location: login.php");
        } else {
            $error = "Gagal mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - My Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#E8DCCB] flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg p-8 rounded-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center text-purple-600">Daftar Akun</h2>
        <?php if (!empty($error)): ?>
            <p class="text-red-500 text-sm mb-4"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-purple-300">
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-purple-300">
            </div>
            <div class="mb-6">
                <label class="block mb-1 text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-purple-300">
            </div>
            <button type="submit" name="register" class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700 transition">Daftar</button>
        </form>
        <p class="text-sm text-center mt-4">Sudah punya akun? <a href="login.php" class="text-purple-500 hover:underline">Login</a></p>
    </div>
</body>
</html>