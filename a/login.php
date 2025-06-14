<?php
session_start();
include 'koneksi.php';

$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan_error']);

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE email='$email'");
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['user_id'] = $data ['id'];
        header("Location: index.php");
    } else {
        $error = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - My Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#E8DCCB] flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg p-8 rounded-md w-full max-w-sm">
        <?php if (!empty($pesan_error)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded mb-4 text-sm text-center">
            <?= $pesan_error ?>
            </div>
        <?php endif; ?>
        <h2 class="text-2xl font-bold mb-6 text-center text-purple-600">Login</h2>
        <?php if (!empty($error)): ?>
            <p class="text-red-500 text-sm mb-4"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-purple-300">
            </div>
            <div class="mb-6">
                <label class="block mb-1 text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring focus:border-purple-300">
            </div>
            <button type="submit" name="login" class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700 transition">Login</button>
        </form>
        <p class="text-sm text-center mt-4">Belum punya akun? <a href="register.php" class="text-purple-500 hover:underline">Daftar</a></p>

        <div class="flex justify-center mt-4">
            <a href="index.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">← Kembali</a>
        </div>
    </div>
</body>
</html>