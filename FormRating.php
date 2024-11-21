<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses data ulasan
if (isset($_POST['id_pelanggan']) && isset($_POST['id_menu']) &&
    isset($_POST['id_transaksi']) && isset($_POST['id_detail_transaksi']) &&
    isset($_POST['rating']) && isset($_POST['komentar'])) {

    $id_pelanggan = $_POST['id_pelanggan'];
    $id_menu = $_POST['id_menu'];
    $id_transaksi = $_POST['id_transaksi'];
    $id_detail_transaksi = $_POST['id_detail_transaksi'];
    $rating = $_POST['rating'];
    $komentar = $conn->real_escape_string($_POST['komentar']);
    $gambar_transfer = '';

    // Cek jika ada file gambar transfer
    if (isset($_FILES['gambar_transfer']) && $_FILES['gambar_transfer']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../BG/storage/app/public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["gambar_transfer"]["name"]);
        if (move_uploaded_file($_FILES["gambar_transfer"]["tmp_name"], $target_file)) {
            $gambar_transfer = "storage/uploads/" . basename($_FILES["gambar_transfer"]["name"]);
        } else {
            echo json_encode([
                'response_status' => "ERROR",
                'response_message' => "Gagal mengunggah gambar."
            ], JSON_PRETTY_PRINT);
            $conn->close();
            exit();
        }
    }

    // Simpan ulasan ke tabel ulasan
    $sql = "INSERT INTO ulasan (id_pelanggan, id_menu, id_transaksi, 
            id_detail_transaksi, rating, komentar, foto_menu_path) 
            VALUES ('$id_pelanggan', '$id_menu', '$id_transaksi', 
            '$id_detail_transaksi', '$rating', '$komentar', '$gambar_transfer')";

    if ($conn->query($sql) === TRUE) {
        $json['response_status'] = "OK";
        $json['response_message'] = "Ulasan berhasil disimpan.";
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Gagal menyimpan ulasan: " . $conn->error;
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Invalid request.";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
?>
