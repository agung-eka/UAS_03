<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query_user = mysqli_query($conn, "SELECT * FROM user WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query_user);

$keranjang = isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : [];

if (empty($keranjang)) {
    echo "<script>alert('Keranjang masih kosong!'); window.location='index.php';</script>";
    exit;
}

// Daftar metode pembayaran dan jasa pengiriman
$metode_pembayaran = [
    ['id' => 'COD', 'label' => 'COD', 'img' => 'img/cod.png'],
    ['id' => 'Gopay', 'label' => 'Gopay', 'img' => 'img/gopay.png'],
    ['id' => 'LinkAja', 'label' => 'LinkAja', 'img' => 'img/linkaja.png'],
    ['id' => 'BNI', 'label' => 'BNI Transfer', 'img' => 'img/bni.png'],
    ['id' => 'Mandiri', 'label' => 'Mandiri', 'img' => 'img/mandiri.png'],
    ['id' => 'BRI', 'label' => 'BRI', 'img' => 'img/bri.png'],
    ['id' => 'OVO', 'label' => 'OVO', 'img' => 'img/ovo.png'],
    ['id' => 'Dana', 'label' => 'Dana', 'img' => 'img/dana.png'],
];

$jasa_pengiriman = [
    ['id' => 'JNE', 'label' => 'JNE Express', 'img' => 'img/jne.png'],
    ['id' => 'SiCepat', 'label' => 'SiCepat', 'img' => 'img/sicepat.png'],
    ['id' => 'J&T', 'label' => 'J&T Express', 'img' => 'img/jnt.png'],
    ['id' => 'AnterAja', 'label' => 'AnterAja', 'img' => 'img/anteraja.png'],
    ['id' => 'POS', 'label' => 'POS Indonesia', 'img' => 'img/pos.png'],
];

// Hitung total harga
$total_harga = 0;
foreach ($keranjang as $id_produk => $jumlah) {
    $query = mysqli_query($conn, "SELECT * FROM produk WHERE id=$id_produk");
    $produk = mysqli_fetch_assoc($query);
    $total_harga += $produk['harga'] * $jumlah;
}

// Proses form checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat_lengkap = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $nama_provinsi = mysqli_real_escape_string($conn, $_POST['nama_provinsi']);
    $nama_kabupaten = mysqli_real_escape_string($conn, $_POST['nama_kabupaten']);
    $nama_kecamatan = mysqli_real_escape_string($conn, $_POST['nama_kecamatan']);
    $nama_desa = mysqli_real_escape_string($conn, $_POST['nama_desa']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
    $pesan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
    $voucher = isset($_POST['voucher']) ? mysqli_real_escape_string($conn, $_POST['voucher']) : '';
    $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $jasa_kirim = mysqli_real_escape_string($conn, $_POST['jasa_kirim']);
    
    // Gabungkan alamat lengkap dengan urutan yang benar
    $alamat = "$nama_desa, $nama_kecamatan, $nama_kabupaten, $nama_provinsi - $alamat_lengkap";    
    // Hitung ongkir (sederhana)
    $ongkir = 10000; // Default
    
    // Hitung biaya layanan berdasarkan metode pembayaran
    $biaya_layanan = 1000;
    switch ($metode_pembayaran) {
        case 'Gopay': $biaya_layanan = 1500; break;
        case 'LinkAja': $biaya_layanan = 1200; break;
        case 'COD': $biaya_layanan = 2000; break;
        case 'BNI':
        case 'Mandiri':  
        case 'BRI': $biaya_layanan = 1000; break;
        case 'OVO': $biaya_layanan = 1600; break;
        case 'Dana': $biaya_layanan = 1400; break;
        default: $biaya_layanan = 1000; break;
    }

    $total_pembayaran = $total_harga + $ongkir + $biaya_layanan;
    $tanggal = date("Y-m-d H:i:s");

    // Query untuk menyimpan pesanan
    $query_pesanan = "INSERT INTO riwayat_pesanan 
        (user_id, nama, alamat, no_hp, pesan, voucher, jasa_kirim, metode_pembayaran, total, tanggal, ongkir, biaya_layanan) 
        VALUES ('$user_id', '$nama', '$alamat', '$no_hp', '$pesan', '$voucher', '$jasa_kirim', '$metode_pembayaran', $total_pembayaran, '$tanggal', $ongkir, $biaya_layanan)";

    if (mysqli_query($conn, $query_pesanan)) {
        $pesanan_id = mysqli_insert_id($conn);
        
        // Simpan detail pesanan
        foreach ($keranjang as $id_produk => $jumlah) {
            $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah) 
                             VALUES ($pesanan_id, $id_produk, $jumlah)";
            mysqli_query($conn, $query_detail);
        }

        // Kosongkan keranjang
        unset($_SESSION['keranjang']);
        
        // Redirect ke halaman riwayat dengan status sukses
        header("Location: riwayat_pesanan.php?status=berhasil");
        exit;
    } else {
        // Tampilkan error jika query gagal
        $error = "Gagal menyimpan pesanan: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Pesanan Anda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #ede9fe, #f5f3ff);
            padding: 2rem;
            margin: 0;
        }
        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 2.5rem;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
        }
        h2 {
            text-align: center;
            font-size: 1.9rem;
            color: #6b21a8;
            margin-bottom: 1.8rem;
        }
        label {
            font-weight: 600;
            margin-top: 1.3rem;
            display: block;
            color: #4b5563;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.7rem;
            margin-top: 0.4rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #9333ea;
            outline: none;
        }
        .dropdown {
            display: none;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.7rem;
            background: #fff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.07);
            margin-top: 0.5rem;
        }
        .dropdown.show {
            display: block;
        }
        .option {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.6rem;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .option:hover {
            background: #f3f4f6;
        }
        .option img {
            width: 34px;
            height: 34px;
        }
        .pilih-box {
            border: 2px solid #d1d5db;
            padding: 0.8rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            cursor: pointer;
            margin-top: 0.6rem;
            transition: 0.3s ease;
        }
        .pilih-box:hover {
            background: #f9fafb;
            border-color: #9333ea;
        }
        .pilih-box img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.3rem;
        }
        .btn {
            display: inline-block;
            text-align: center;
            background: linear-gradient(to right, #9333ea, #7e22ce);
            color: white;
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: linear-gradient(to right, #7e22ce, #6b21a8);
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin: 0.5rem 0;
            padding: 0.6rem 1rem;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .flex {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .summary-box {
            margin-top: 1.5rem;
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 10px;
        }
        .error {
            color: #ef4444;
            background: #fee2e2;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.3);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Checkout Pesanan Anda</h2>
    
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" action="checkout.php">
        <label>Nama:</label>
        <input type="text" name="nama" value="" required placeholder="Masukkan Nama">

        <label>Provinsi:</label>
        <select name="provinsi" id="provinsi" required>
            <option value="">Pilih Provinsi</option>
        </select>

        <label>Kabupaten/Kota:</label>
        <select name="kabupaten" id="kabupaten" required disabled>
            <option value="">Pilih Kabupaten/Kota</option>
        </select>

        <label>Kecamatan:</label>
        <select name="kecamatan" id="kecamatan" required disabled>
            <option value="">Pilih Kecamatan</option>
        </select>

        <label>Desa/Kelurahan:</label>
        <select name="desa" id="desa" required disabled>
            <option value="">Pilih Desa/Kelurahan</option>
        </select>

        <input type="hidden" name="nama_provinsi" id="nama-provinsi">
        <input type="hidden" name="nama_kabupaten" id="nama-kabupaten">
        <input type="hidden" name="nama_kecamatan" id="nama-kecamatan">
        <input type="hidden" name="nama_desa" id="nama-desa">

        <label>Alamat Lengkap (Jalan, Nomor Rumah, RT/RW):</label>
        <textarea name="alamat_lengkap" required placeholder="Contoh: Jl. Merdeka No. 10, RT 01/RW 02"></textarea>

        <label>Nomor HP:</label>
        <input type="text" name="nomor_hp" required placeholder="Contoh: 081234567890">

        <label>Catatan (Opsional):</label>
        <textarea name="catatan" placeholder="Contoh: Tolong dibungkus rapat"></textarea>

        <label>Kode Voucher (Opsional):</label>
        <input type="text" name="voucher" placeholder="Masukkan kode voucher jika ada">

        <!-- Jasa Pengiriman -->
        <label>Jasa Pengiriman:</label>
        <div class="pilih-box" onclick="toggleJasaKirim()">
            <img id="jasa-kirim-img" src="img/jne.png"> 
            <span id="jasa-kirim-label">JNE Express</span>
        </div>
        <div id="jasa-kirim-options" class="dropdown">
            <?php foreach ($jasa_pengiriman as $jasa): ?>
            <div class="option" onclick="selectJasaKirim('<?= $jasa['id'] ?>','<?= $jasa['label'] ?>','<?= $jasa['img'] ?>')">
                <img src="<?= $jasa['img'] ?>"> <?= $jasa['label'] ?>
            </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="jasa_kirim" id="jasa-kirim-input" value="JNE">

        <!-- Metode Pembayaran -->
        <label>Metode Pembayaran:</label>
        <div class="pilih-box" onclick="toggleMetodeBayar()">
            <img id="metode-bayar-img" src="img/cod.png"> 
            <span id="metode-bayar-label">COD</span>
        </div>
        <div id="metode-bayar-options" class="dropdown">
            <?php foreach ($metode_pembayaran as $metode): ?>
            <div class="option" onclick="selectMetodeBayar('<?= $metode['id'] ?>','<?= $metode['label'] ?>','<?= $metode['img'] ?>')">
                <img src="<?= $metode['img'] ?>"> <?= $metode['label'] ?>
            </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="metode_pembayaran" id="metode-bayar-input" value="COD">

        <!-- Ringkasan Produk -->
        <h2>Produk Dipesan</h2>
        <ul>
        <?php foreach ($keranjang as $id_produk => $jumlah): 
            $q = mysqli_query($conn, "SELECT * FROM produk WHERE id=$id_produk");
            $p = mysqli_fetch_assoc($q);
            $subtotal = $p['harga'] * $jumlah;
        ?>
            <li><?= htmlspecialchars($p['nama']) ?> x<?= $jumlah ?> - Rp<?= number_format($subtotal,0,',','.') ?></li>
        <?php endforeach; ?>
        </ul>

        <!-- Rincian Pembayaran -->
        <div class="summary-box">
            <p>Total Produk: Rp<?= number_format($total_harga, 0, ',', '.') ?></p>
            <p>Ongkos Kirim: <span id="ongkir-display">Rp10.000</span></p>
            <p>Biaya Layanan: <span id="biaya-layanan-display">Rp1.000</span></p>
            <p><b>Total Bayar: <span id="total-pembayaran-display">Rp<?= number_format($total_harga + 10000 + 1000, 0, ',', '.') ?></span></b></p>
        </div>

        <div class="flex">
            <a href="keranjang.php" class="btn">← Kembali</a>
            <button type="submit" class="btn">🛒 Buat Pesanan Sekarang</button>
        </div>
    </form>
</div>

<script>
    // Fungsi untuk dropdown alamat
    const prov = document.getElementById('provinsi');
    const kab = document.getElementById('kabupaten');
    const kec = document.getElementById('kecamatan');
    const des = document.getElementById('desa');

    // Load provinsi
    fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
        .then(response => response.json())
        .then(provinces => {
            // Urutkan provinsi berdasarkan nama
            provinces.sort((a, b) => a.name.localeCompare(b.name));
            
            // Tambahkan opsi provinsi
            provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.id;
                option.textContent = province.name;
                prov.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading provinces:', error);
            prov.innerHTML += '<option value="">Gagal memuat data provinsi</option>';
        });

    // Load kabupaten saat provinsi dipilih
    prov.addEventListener('change', function() {
        if(!this.value) {
            kab.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            kab.disabled = true;
            kec.innerHTML = '<option value="">Pilih Kecamatan</option>';
            kec.disabled = true;
            des.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            des.disabled = true;
            return;
        }
        
        kab.disabled = true;
        kab.innerHTML = '<option value="">Memuat...</option>';
        
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${this.value}.json`)
            .then(response => response.json())
            .then(regencies => {
                // Urutkan kabupaten berdasarkan nama
                regencies.sort((a, b) => a.name.localeCompare(b.name));
                
                kab.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
                regencies.forEach(regency => {
                    const option = document.createElement('option');
                    option.value = regency.id;
                    option.textContent = regency.name;
                    kab.appendChild(option);
                });
                kab.disabled = false;
                
                // Reset kecamatan dan desa
                kec.innerHTML = '<option value="">Pilih Kecamatan</option>';
                kec.disabled = true;
                des.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                des.disabled = true;
            })
            .catch(error => {
                console.error('Error loading regencies:', error);
                kab.innerHTML = '<option value="">Gagal memuat data kabupaten</option>';
            });
    });

    // Load kecamatan saat kabupaten dipilih
    kab.addEventListener('change', function() {
        if(!this.value) {
            kec.innerHTML = '<option value="">Pilih Kecamatan</option>';
            kec.disabled = true;
            des.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            des.disabled = true;
            return;
        }
        
        kec.disabled = true;
        kec.innerHTML = '<option value="">Memuat...</option>';
        
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${this.value}.json`)
            .then(response => response.json())
            .then(districts => {
                // Urutkan kecamatan berdasarkan nama
                districts.sort((a, b) => a.name.localeCompare(b.name));
                
                kec.innerHTML = '<option value="">Pilih Kecamatan</option>';
                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    kec.appendChild(option);
                });
                kec.disabled = false;
                
                // Reset desa
                des.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                des.disabled = true;
            })
            .catch(error => {
                console.error('Error loading districts:', error);
                kec.innerHTML = '<option value="">Gagal memuat data kecamatan</option>';
            });
    });

    // Load desa saat kecamatan dipilih
    kec.addEventListener('change', function() {
        if(!this.value) {
            des.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            des.disabled = true;
            return;
        }
        
        des.disabled = true;
        des.innerHTML = '<option value="">Memuat...</option>';
        
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${this.value}.json`)
            .then(response => response.json())
            .then(villages => {
                // Urutkan desa berdasarkan nama
                villages.sort((a, b) => a.name.localeCompare(b.name));
                
                des.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                villages.forEach(village => {
                    const option = document.createElement('option');
                    option.value = village.id;
                    option.textContent = village.name;
                    des.appendChild(option);
                });
                des.disabled = false;
            })
            .catch(error => {
                console.error('Error loading villages:', error);
                des.innerHTML = '<option value="">Gagal memuat data desa</option>';
            });
    });

        // Tambahkan saat memilih provinsi
        prov.addEventListener('change', function() {
            if(this.value) {
                const selectedProv = this.options[this.selectedIndex].text;
                document.getElementById('nama-provinsi').value = selectedProv;
            }
        });

        // Lakukan hal serupa untuk kabupaten, kecamatan, dan desa
        kab.addEventListener('change', function() {
            if(this.value) {
                const selectedKab = this.options[this.selectedIndex].text;
                document.getElementById('nama-kabupaten').value = selectedKab;
            }
        });

        kec.addEventListener('change', function() {
            if(this.value) {
                const selectedKec = this.options[this.selectedIndex].text;
                document.getElementById('nama-kecamatan').value = selectedKec;
            }
        });

        des.addEventListener('change', function() {
            if(this.value) {
                const selectedDes = this.options[this.selectedIndex].text;
                document.getElementById('nama-desa').value = selectedDes;
            }
        });

    // Fungsi untuk dropdown jasa kirim
    function toggleJasaKirim() {
        document.getElementById('jasa-kirim-options').classList.toggle('show');
        // Tutup dropdown metode bayar jika terbuka
        document.getElementById('metode-bayar-options').classList.remove('show');
    }

    function selectJasaKirim(id, label, img) {
        document.getElementById('jasa-kirim-input').value = id;
        document.getElementById('jasa-kirim-label').textContent = label;
        document.getElementById('jasa-kirim-img').src = img;
        toggleJasaKirim();
    }

    // Fungsi untuk dropdown metode pembayaran
    function toggleMetodeBayar() {
        document.getElementById('metode-bayar-options').classList.toggle('show');
        // Tutup dropdown jasa kirim jika terbuka
        document.getElementById('jasa-kirim-options').classList.remove('show');
    }

    function selectMetodeBayar(id, label, img) {
        document.getElementById('metode-bayar-input').value = id;
        document.getElementById('metode-bayar-label').textContent = label;
        document.getElementById('metode-bayar-img').src = img;
        toggleMetodeBayar();
        updateTotal();
    }

    // Fungsi untuk update total pembayaran
    function updateTotal() {
        const metode = document.getElementById('metode-bayar-input').value;
        let biaya = 1000;
        
        if (metode === 'COD') biaya = 2000;
        else if (metode === 'Gopay') biaya = 1500;
        else if (metode === 'LinkAja') biaya = 1200;
        else if (metode === 'OVO') biaya = 1600;
        else if (metode === 'Dana') biaya = 1400;

        const ongkir = 10000;
        const totalProduk = <?= $total_harga ?>;
        const totalBayar = totalProduk + ongkir + biaya;

        document.getElementById('biaya-layanan-display').textContent = 'Rp' + biaya.toLocaleString('id-ID');
        document.getElementById('total-pembayaran-display').textContent = 'Rp' + totalBayar.toLocaleString('id-ID');
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.pilih-box') && !event.target.closest('.dropdown')) {
            document.getElementById('jasa-kirim-options').classList.remove('show');
            document.getElementById('metode-bayar-options').classList.remove('show');
        }
    });
</script>
</body>
</html>