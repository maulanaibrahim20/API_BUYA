<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

$conn = new mysqli("localhost", "root", "", "db_buya");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$json = array(
    "response_status" => "OK",
    "response_message" => ''
);

$id_pelanggan = isset($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : '';
$id_menu = isset($_POST['id_menu']) ? $_POST['id_menu'] : '';
$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : ''; // New action parameter

if (empty($id_pelanggan)) {
    $json['response_status'] = "ERROR";
    $json['response_message'] = "Missing required fields";
    echo json_encode($json, JSON_PRETTY_PRINT);
    exit();
}

if ($action == 'delete' && !empty($id_menu)) {
    // Delete item from cart
    $sql = "DELETE FROM keranjang WHERE id_menu = '$id_menu' AND id_pelanggan = '$id_pelanggan'";
    if ($conn->query($sql) === TRUE) {
        $json['response_message'] = "Item removed from cart";
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Error: " . $sql . "<br>" . $conn->error;
    }
} elseif ($action == 'update' && !empty($id_menu) && isset($quantity)) {
    // Update quantity in cart
    $menu_sql = $conn->query("SELECT harga_menu FROM menu WHERE id_menu = '$id_menu'");
    if ($menu_sql->num_rows > 0) {
        $menu = $menu_sql->fetch_object();
        $harga_menu = $menu->harga_menu;

        if ($quantity <= 0) {
            // Remove item if quantity is less than or equal to 0
            $sql = "DELETE FROM keranjang WHERE id_menu = '$id_menu' AND id_pelanggan = '$id_pelanggan'";
        } else {
            // Update quantity and total_harga
            $total_harga = $harga_menu * $quantity;
            $sql = "UPDATE keranjang SET quantity = '$quantity', total_harga = '$total_harga' WHERE id_menu = '$id_menu' AND id_pelanggan = '$id_pelanggan'";
        }

        if ($conn->query($sql) === TRUE) {
            $json['response_message'] = "Cart updated";
        } else {
            $json['response_status'] = "ERROR";
            $json['response_message'] = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Menu item not found";
    }
} elseif (!empty($id_menu) && !empty($quantity)) {
    // Insert into cart
    // Get menu price
    $menu_sql = $conn->query("SELECT harga_menu FROM menu WHERE id_menu = '$id_menu'");
    if ($menu_sql->num_rows > 0) {
        $menu = $menu_sql->fetch_object();
        $total_harga = $menu->harga_menu * $quantity;

        // Check if item already in cart
        $check_sql = $conn->query("SELECT * FROM keranjang WHERE id_menu = '$id_menu' AND id_pelanggan = '$id_pelanggan'");
        if ($check_sql->num_rows > 0) {
            // Update quantity and total_harga if item already exists
            $existing_item = $check_sql->fetch_object();
            $new_quantity = $existing_item->quantity + $quantity; // Add the new quantity to the existing one

            // Make sure the new quantity is at least 50
            if ($new_quantity < 50) {
                $json['response_status'] = "ERROR";
                $json['response_message'] = "Quantity must be at least 50.";
            } else {
                $new_total_harga = $menu->harga_menu * $new_quantity;
                $sql = "UPDATE keranjang SET quantity = '$new_quantity', total_harga = '$new_total_harga' WHERE id_menu = '$id_menu' AND id_pelanggan = '$id_pelanggan'";
                if ($conn->query($sql) === TRUE) {
                    $json['response_message'] = "Cart updated";
                } else {
                    $json['response_status'] = "ERROR";
                    $json['response_message'] = "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        } else {
            // Insert new record
            if ($quantity < 50) {
                $json['response_status'] = "ERROR";
                $json['response_message'] = "Quantity must be at least 50.";
            } else {
                $sql = "INSERT INTO keranjang (id_menu, quantity, total_harga, id_pelanggan) VALUES ('$id_menu', '$quantity', '$total_harga', '$id_pelanggan')";
                if ($conn->query($sql) === TRUE) {
                    $json['response_message'] = "Item added to cart";
                } else {
                    $json['response_status'] = "ERROR";
                    $json['response_message'] = "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "Menu item not found";
    }
} else {
    // Display cart data
    $sql = "SELECT k.id_menu, m.nama_menu, m.harga_menu, k.quantity, (m.harga_menu * k.quantity) AS total_harga, m.foto_menu_path
            FROM keranjang k
            JOIN menu m ON k.id_menu = m.id_menu
            WHERE k.id_pelanggan = '$id_pelanggan'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = array();
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $json['data'] = $data;
    } else {
        $json['response_status'] = "ERROR";
        $json['response_message'] = "No items found";
    }
}

echo json_encode($json, JSON_PRETTY_PRINT);
$conn->close();
?>
