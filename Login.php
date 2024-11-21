<?php
// Mengambil data dari form
$telepon = $_POST['telepon'];
$password = $_POST['password'];

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "db_buya");

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mengecek apakah telepon dan password cocok di tabel pelanggan
$sqlPelanggan = "SELECT id_pelanggan, nama, telepon FROM pelanggan WHERE telepon = ? AND password = ?";
$stmtPelanggan = $conn->prepare($sqlPelanggan);
$stmtPelanggan->bind_param("ss", $telepon, $password);
$stmtPelanggan->execute();
$resultPelanggan = $stmtPelanggan->get_result();

// Query untuk mengecek apakah telepon dan password cocok di tabel driver
$sqlDriver = "SELECT id_driver, nama, telepon FROM driver WHERE telepon = ? AND password = ?";
$stmtDriver = $conn->prepare($sqlDriver);
$stmtDriver->bind_param("ss", $telepon, $password);
$stmtDriver->execute();
$resultDriver = $stmtDriver->get_result();

// Memeriksa apakah login berhasil sebagai pelanggan
if ($resultPelanggan->num_rows > 0) {
    // Mengambil data pengguna
    $user = $resultPelanggan->fetch_assoc();
    
    // Menyimpan informasi pengguna dalam session
    session_start();
    $_SESSION['user_id'] = $user['id_pelanggan'];
    $_SESSION['role'] = 'pelanggan'; // Menyimpan jenis pengguna
    
    // Redirect ke halaman pelanggan
    header("Location: Home_page.php");
    exit(); // Menghentikan eksekusi setelah redirect

// Memeriksa apakah login berhasil sebagai driver
} elseif ($resultDriver->num_rows > 0) {
    // Mengambil data pengguna
    $user = $resultDriver->fetch_assoc();
    
    // Menyimpan informasi pengguna dalam session
    session_start();
    $_SESSION['user_id'] = $user['id_driver'];
    $_SESSION['role'] = 'driver'; // Menyimpan jenis pengguna

    // Redirect ke halaman driver
    header("Location: Home_driver.php");
    exit(); // Menghentikan eksekusi setelah redirect

} else {
    // Jika telepon atau password salah
    echo "<script>alert('Nomor Telepon atau Password salah.'); window.location.href = 'login_page.php';</script>";
}

$conn->close();
?>
