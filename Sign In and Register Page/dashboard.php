
<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user['status'] != 'approved') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($user['role'] != 'admin') {
    header("Location: user_dashboard.php");
    exit();
}

$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

$total_pending = $conn->query("SELECT COUNT(*) AS total FROM users WHERE status='pending'")->fetch_assoc()['total'];

$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: black;
        }

        nav {
            margin-bottom: 35px;
        }

        nav a {
            color: black;
            text-decoration: none;
            margin-right: 15px;
        }

        nav a:visited {
            color: black;
        }

        nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="users.php">User Management</a>
    <a href="logout.php">Logout</a>
</nav>

<br><br>

<h1>Welcome!</h1>
<h3>Hello, <?= htmlspecialchars($user['fullname']) ?>!</h3>

<br>

<table width="100%" cellpadding="15" cellspacing="15">
    <tr>
        <td width="33%" height="120" align="center" bgcolor="blue">
            <font color="white">
                <h2>Total of Users</h2>
                <h1><?= $total_users ?></h1>
            </font>
        </td>

        <td width="33%" height="120" align="center" bgcolor="#E6A052">
            <font color="white">
                <h2>Total of Not Approved Users</h2>
                <h1><?= $total_pending ?></h1>
            </font>
        </td>

        <td width="33%" height="120" align="center" bgcolor="green">
            <font color="white">
                <h2>Total of Products</h2>
                <h1><?= $total_products ?></h1>
            </font>
        </td>
    </tr>
</table>

</body>
</html>
