<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$json = array(
    "response_status" => "OK",
    "response_message" => "",
    "data" => array()
);

if (isset($_GET['kategori'])) {
    $kategori = $_GET['kategori'];
    $sql = "SELECT id_menu, nama_menu, harga_menu, deskripsi, foto_menu_path FROM menus WHERE kategori = '$kategori'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
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
        $json['response_message'] = "Tidak ada data";
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Kategori tidak ditemukan";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
?>
