<?php
$conn = new mysqli("localhost", "root", "", "line_b2b");
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
?>