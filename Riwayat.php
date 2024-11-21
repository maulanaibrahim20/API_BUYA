<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mendapatkan id_pelanggan dari request
$id_pelanggan = isset($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : '';

if (empty($id_pelanggan)) {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "ID Pelanggan tidak tersedia";
    echo json_encode($json, JSON_PRETTY_PRINT);
    $conn->close();
    exit();
}

// Query untuk mendapatkan data transaksi dan detail menu
$sql = "SELECT 
            t.id_transaksi, t.status_transaksi, t.metode_pembayaran, 
            t.tanggal_pesan, t.total_harga, t.alamat,
            dt.quantity, m.nama_menu, (m.harga_menu * dt.quantity) AS harga_total
        FROM transaksi t
        LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
        LEFT JOIN menu m ON dt.id_menu = m.id_menu
        WHERE t.id_pelanggan = '$id_pelanggan'";

$result = $conn->query($sql);

$transaksiList = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_transaksi = $row['id_transaksi'];
        
        // Menyimpan data transaksi utama
        if (!isset($transaksiList[$id_transaksi])) {
            $transaksiList[$id_transaksi] = [
                'status_transaksi' => $row['status_transaksi'],
                'metode_pembayaran' => $row['metode_pembayaran'],
                'tanggal_pesan' => $row['tanggal_pesan'],
                'total_harga' => $row['total_harga'],
                'alamat' => $row['alamat'],
                'detail_menu' => []
            ];
        }
        
        // Menambahkan detail menu
        $transaksiList[$id_transaksi]['detail_menu'][] = [
            'nama_menu' => $row['nama_menu'],
            'quantity' => $row['quantity'],
            'harga_total' => $row['harga_total']
        ];
    }
}

echo json_encode(array_values($transaksiList), JSON_PRETTY_PRINT);

// Tutup koneksi database
$conn->close();
?>
