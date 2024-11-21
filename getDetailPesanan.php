<?php
header("Content-Type: application/json");
include 'config.php'; // Pastikan Anda mengimpor koneksi ke database

if (isset($_GET['id_transaksi'])) {
    $id_transaksi = $_GET['id_transaksi'];

    $query = "SELECT 
                dt.quantity, 
                m.nama_menu, 
                m.harga_menu 
              FROM 
                detail_transaksi dt 
              JOIN 
                menu m ON dt.id_menu = m.id_menu 
              WHERE 
                dt.id_transaksi = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $detailMenu = array();
        while ($row = $result->fetch_assoc()) {
            $detailMenu[] = $row;
        }
        echo json_encode(array('detailMenu' => $detailMenu));
    } else {
        echo json_encode(array('error' => 'Tidak ada detail pesanan ditemukan.'));
    }
} else {
    echo json_encode(array('error' => 'ID transaksi tidak ditemukan.'));
}
?>
