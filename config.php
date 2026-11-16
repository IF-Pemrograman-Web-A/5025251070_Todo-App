<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "todo_app";

$conn = @mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'api.php') !== false) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => "Database connection failed"
        ));
        exit;
    }
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>