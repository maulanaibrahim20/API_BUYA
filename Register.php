<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die(json_encode(array("response_status" => "ERROR", "response_message" => "Database connection failed")));
}
date_default_timezone_set('Asia/Jakarta');

$json = array(
    "response_status" => "OK",
    "response_message" => ''
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $telepon = isset($_POST['telepon']) ? $_POST['telepon'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Cek apakah semua data terisi
    if (!empty($nama) && !empty($telepon) && !empty($password)) {
        // Cek apakah nomor telepon sudah terdaftar
        $sql = $conn->query("SELECT id_pelanggan FROM pelanggan WHERE telepon = '$telepon'");
        $jml = $sql->num_rows;

        if ($jml == 0) {
            // Hash password sebelum menyimpannya ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan data pelanggan baru ke database
            $insert = $conn->query("INSERT INTO pelanggan (nama, telepon, password) VALUES ('$nama', '$telepon', '$hashed_password')");
            if ($insert) {
                $json['response_message'] = "Registration successful";
            } else {
                $json['response_status'] = "ERROR";
                $json['response_message'] = "Registration failed";
            }
        } else {
            $json['response_status'] = "ERROR";
            $json['response_message'] = "Nomor Telepon sudah terdaftar";
        }
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Semua data harus diisi";
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Invalid request method";
}
echo json_encode($json, JSON_PRETTY_PRINT);
?>
