<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$json = array(
    "response_status" => "OK",
    "response_message" => '',
    "data" => array()
);

// Ganti ID_PELANGGAN dengan ID pelanggan yang sesuai
$id_pelanggan = 1; // Contoh, ganti dengan ID pelanggan yang sedang login

// Mengambil data rating
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = $conn->query("
        SELECT t.id_transaksi, t.tanggal_pesan, t.tanggal_transaksi, dt.id_menu, m.nama_menu
        FROM transaksi t
        JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
        JOIN menu m ON dt.id_menu = m.id_menu
        WHERE t.id_pelanggan = $id_pelanggan
        ORDER BY t.tanggal_pesan DESC
    ");

    if ($sql->num_rows > 0) {
        while ($rs = $sql->fetch_object()) {
            $json['data'][] = array(
                "id_transaksi" => $rs->id_transaksi,
                "nama_menu" => $rs->nama_menu,
                "tanggal_pesan" => $rs->tanggal_pesan,
                "tanggal_transaksi" => $rs->tanggal_transaksi,
            );
        }
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Tidak ada data";
    }
    echo json_encode($json, JSON_PRETTY_PRINT);
    exit;
}

// Menyimpan ulasan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mendapatkan data dari request
    $id_menu = isset($_POST['id_menu']) ? (int)$_POST['id_menu'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $komentar = isset($_POST['komentar']) ? $conn->real_escape_string($_POST['komentar']) : '';
    $foto_menu_path = isset($_POST['foto_menu_path']) ? $conn->real_escape_string($_POST['foto_menu_path']) : '';
    $id_transaksi = isset($_POST['id_transaksi']) ? (int)$_POST['id_transaksi'] : 0;
    $id_detail_transaksi = isset($_POST['id_detail_transaksi']) ? (int)$_POST['id_detail_transaksi'] : 0;

    // Menyimpan ulasan ke database
    $sql = "INSERT INTO ulasan (id_menu, id_transaksi, id_detail_transaksi, rating, komentar, foto_menu_path) VALUES ($id_menu, $id_transaksi, $id_detail_transaksi, $rating, '$komentar', '$foto_menu_path')";

    if ($conn->query($sql) === TRUE) {
        $json['response_message'] = 'Ulasan berhasil disimpan';
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Gagal menyimpan ulasan: " . $conn->error;
    }
    echo json_encode($json);
    exit;
}

$conn->close();
?>
