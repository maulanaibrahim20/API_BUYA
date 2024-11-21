<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "posdu");
mysqli_connect_errno();
date_default_timezone_set('Asia/Jakarta');

$json = array(
    "response_status" => "OK",
    "response_message" => '',
    "data" => array()
);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = isset($_GET['username']) ? $_GET['username'] : '';
    $password = isset($_GET['password']) ? $_GET['password'] : '';

    // Log request for debugging
    error_log("Username: $username, Password: $password");

    $sql = $conn->query("SELECT `id`, `username`, `password`, `email`, `NIK`, `NOKK` FROM `users` WHERE `username`='$username' AND `password`='$password'");
    $jml = $sql->num_rows;

    if ($jml > 0) {
        while ($rs = $sql->fetch_object()) {
            $arr_row = array(
                'ID' => $rs->id,
                'username' => $rs->username,
                'email' => $rs->email,
                'NIK' => $rs->NIK,
                'NOKK' => $rs->NOKK
            );
            $json['data'][] = $arr_row;
        }
    } else {
        $json['response_status'] = "Error";
        $json['response_message'] = "Username atau Password Salah";
    }
} else {
    $json['response_status'] = "Error";
    $json['response_message'] = "Invalid request method";
}

print json_encode($json, JSON_PRETTY_PRINT);
?>
