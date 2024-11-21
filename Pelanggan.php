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
    "response_message" => '',
    "data" => array()
);

// Metode GET untuk autentikasi pelanggan
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Cek apakah telepon dan password ada dalam parameter
    if (isset($_GET['telepon']) && isset($_GET['password'])) {
        $telepon = $_GET['telepon'];
        $password = $_GET['password'];

        // Query untuk mengecek data pelanggan berdasarkan telepon dan password
        $sql = "SELECT id_pelanggan, nama, telepon FROM pelanggan WHERE telepon = '$telepon' AND password = '$password'";
        $result = $conn->query($sql);

        // Jika pelanggan ditemukan
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $json['data'][] = $row; // Simpan data pelanggan dalam array
        } else {
            // Jika data pelanggan tidak ditemukan
            $json['response_status'] = "ERROR";
            $json['response_message'] = "Telepon atau Password salah";
        }
    } else {
        // Jika parameter telepon atau password tidak ada
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Parameter Telepon atau Password tidak lengkap";
    }
} else {
    // Jika request method bukan GET
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Invalid request method";
}

// Mengirimkan response dalam format JSON
echo json_encode($json, JSON_PRETTY_PRINT);

?>
