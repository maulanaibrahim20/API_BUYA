<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Koneksi database
$conn = new mysqli("localhost", "root", "Bayubiantara1", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil parameter kategori dari request
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : null;

$json = array(
    "response_status" => "OK",
    "response_message" => "",
    "data" => array()
);

if (!$kategori || !in_array($kategori, ['paketan', 'prasmanan'])) {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Kategori tidak valid atau tidak dipilih.";
    echo json_encode($json, JSON_PRETTY_PRINT);
    exit;
}

// Query untuk menghitung jumlah pesanan berdasarkan id_menu dengan status "Selesai" dan kategori
$sql = "SELECT 
            menu.id_menu, 
            menu.nama_menu, 
            menu.harga_menu, 
            menu.kategori_menu,
            COUNT(pesanan.id_menu) AS jumlah_pesanan
        FROM pesanan
        INNER JOIN menu ON pesanan.id_menu = menu.id_menu
        WHERE pesanan.status_pesanan = 'selesai'
          AND pesanan.metode_pembayaran = 'Lunas'
          AND menu.kategori_menu = ?
        GROUP BY pesanan.id_menu
        ORDER BY jumlah_pesanan DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $kategori);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $json['data'][] = array(
            "id_menu" => $row['id_menu'],
            "nama_menu" => $row['nama_menu'],
            "harga_menu" => $row['harga_menu'],
            "kategori" => $row['kategori_menu'],
            "jumlah_pesanan" => $row['jumlah_pesanan']
        );
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Tidak ada menu best seller untuk kategori yang dipilih.";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$stmt->close();
$conn->close();
