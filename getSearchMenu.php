<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$json = array(
    "status" => 200,
    "response_status" => "OK",
    "data" => array()
);

// Parameter sorting default
$sort_order = "ASC"; // Default sorting: termurah ke termahal
if (isset($_GET['sort']) && strtolower($_GET['sort']) == "desc") {
    $sort_order = "DESC"; // Ubah ke termahal ke termurah jika parameter "sort=desc"
}

if (isset($_GET['search'])) {
    $keyword = $conn->real_escape_string($_GET['search']);

    $sql = "SELECT id_menu, nama_menu, harga_menu, deskripsi, foto_menu_path 
            FROM menu 
            WHERE nama_menu LIKE '%$keyword%' OR deskripsi LIKE '%$keyword%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $arr_row = array(
                "id_menu" => $row['id_menu'],
                "nama_menu" => $row['nama_menu'],
                "harga_menu" => $row['harga_menu'],
                "deskripsi" => $row['deskripsi'],
                "foto_menu_path" => "http://10.0.164.244/buyaglg/public/storage/uploads/" . $row['foto_menu_path']
            );
            $json['data'][] = $arr_row;
        }
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Menu tidak ditemukan";
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Parameter tidak ditemukan";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
