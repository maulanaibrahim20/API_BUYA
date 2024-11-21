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

// Cek parameter sort
$sort_order = "ASC"; // Default: termurah ke termahal
if (isset($_GET['sort']) && strtolower($_GET['sort']) == "desc") {
    $sort_order = "DESC"; // Ubah ke termahal ke termurah
}

// Query untuk sorting harga_menu
$sql = "SELECT id_menu, nama_menu, harga_menu, deskripsi, foto_menu_path 
        FROM menu 
        ORDER BY harga_menu $sort_order";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $json['data'][] = array(
            "id_menu" => $row['id_menu'],
            "nama_menu" => $row['nama_menu'],
            "harga_menu" => $row['harga_menu'],
            "deskripsi" => $row['deskripsi'],
            "foto_menu_path" => "http://10.0.164.244/buyaglg/public/storage/uploads/" . $row['foto_menu_path']
        );
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Tidak ada data";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
