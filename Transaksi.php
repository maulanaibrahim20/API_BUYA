<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses data pembayaran
if (isset($_POST['id_pelanggan']) && isset($_POST['total_harga']) && 
    isset($_POST['metode_pembayaran']) && isset($_POST['tanggal_pesan']) && 
    isset($_POST['alamat']) && isset($_POST['bayar']) && isset($_POST['keranjang'])) {

    $id_pelanggan = $_POST['id_pelanggan'];
    $total_harga = $_POST['total_harga'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $tanggal_pesan = $_POST['tanggal_pesan'];
    $alamat = $_POST['alamat'];
    $bayar = $_POST['bayar'];
    $tanggal_transaksi = date('Y-m-d H:i:s');
    $keranjang = json_decode($_POST['keranjang'], true);
    $gambar_transfer = '';

    // Cek jika ada file gambar transfer
    if (isset($_FILES['gambar_transfer'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["gambar_transfer"]["name"]);
        if (move_uploaded_file($_FILES["gambar_transfer"]["tmp_name"], $target_file)) {
            $gambar_transfer = basename($_FILES["gambar_transfer"]["name"]);
        } else {
            echo json_encode([
                'response_status' => "ERROR",
                'response_message' => "Gagal mengunggah gambar transfer."
            ], JSON_PRETTY_PRINT);
            $conn->close();
            exit();
        }
    }

    // Simpan transaksi ke tabel transaksi
    $sql = "INSERT INTO transaksi (id_pelanggan, total_harga, gambar_transfer, 
            tanggal_pesan, tanggal_transaksi, status_transaksi, metode_pembayaran, 
            alamat, bayar) VALUES ('$id_pelanggan', '$total_harga', 
            '$gambar_transfer', '$tanggal_pesan', '$tanggal_transaksi', 'PENDING', 
            '$metode_pembayaran', '$alamat', '$bayar')";

    if ($conn->query($sql) === TRUE) {
        $id_transaksi = $conn->insert_id;

        // Simpan detail transaksi
        foreach ($keranjang as $item) {
            $id_menu = $item['id_menu'];
            $quantity = $item['quantity'];

            // Ambil harga_menu dari tabel menu
            $menu_sql = "SELECT harga_menu FROM menu WHERE id_menu = '$id_menu'";
            $menu_result = $conn->query($menu_sql);

            if ($menu_result->num_rows > 0) {
                $menu_data = $menu_result->fetch_assoc();
                $harga_menu = $menu_data['harga_menu'];
                $harga_total = $harga_menu * $quantity; // Kalkulasi harga

                // Simpan ke detail_transaksi
                $detail_sql = "INSERT INTO detail_transaksi 
                               (id_transaksi, id_menu, quantity, harga) 
                               VALUES ('$id_transaksi', '$id_menu', '$quantity', '$harga_total')";
                $conn->query($detail_sql);
            }
        }

        // Hapus item keranjang setelah transaksi berhasil
        $clear_cart_sql = "DELETE FROM keranjang WHERE id_pelanggan = '$id_pelanggan'";
        if ($conn->query($clear_cart_sql) === TRUE) {
            $json['response_status'] = "OK";
            $json['response_message'] = "Pembayaran berhasil dan detail transaksi disimpan.";
        } else {
            $json['response_status'] = "ERROR";
            $json['response_message'] = "Pembayaran berhasil, tapi gagal membersihkan keranjang.";
        }
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Gagal menyimpan transaksi: " . $conn->error;
    }
} else {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Invalid request";
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
?>
