
<?php
$conn = mysqli_connect("localhost", "root", "", "signin_register_db");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
