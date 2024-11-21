<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Koneksi database
$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$json = array(
    "response_status" => "OK",
    "response_message" => "",
    "data" => array()
);

// Query untuk menghitung jumlah pesanan berdasarkan id_menu dengan status "Selesai"
$sql = "SELECT 
            menu.id_menu, 
            menu.nama_menu, 
            menu.harga_menu, 
            COUNT(pesanan.id_menu) AS jumlah_pesanan
        FROM pesanan
        INNER JOIN menu ON pesanan.id_menu = menu.id_menu
        WHERE pesanan.status_pesanan = 'selesai'
          AND pesanan.metode_pembayaran = 'Lunas'
        GROUP BY pesanan.id_menu
        ORDER BY jumlah_pesanan DESC
        LIMIT 1";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $json['data'][] = array(
            "id_menu" => $row['id_menu'],
            "nama_menu" => $row['nama_menu'],
            "harga_menu" => $row['harga_menu'],
            "jumlah_pesanan" => $row['jumlah_pesanan']
        );
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Tidak ada menu best seller.";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
