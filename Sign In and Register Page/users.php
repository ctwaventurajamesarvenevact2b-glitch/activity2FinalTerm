
<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT role, status FROM users WHERE id=?");
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

$message = "";

if (isset($_POST['action'], $_POST['user_id'])) {
    $user_id = (int) $_POST['user_id'];
    $action = $_POST['action'];

    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot change your own account status.";
    } elseif (in_array($action, ['approve', 'disable', 'enable'])) {

        $status = ($action == 'disable') ? 'disabled' : 'approved';

        $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $user_id);

        if ($stmt->execute()) {
            header("Location: users.php?success=1");
            exit();
        } else {
            $message = "Unable to update user.";
        }
    }
}

if (isset($_GET['success'])) {
    $message = "User status updated successfully!";
}

$users = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>

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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #d58b9b;
            font-weight: normal;
        }

        button {
            cursor: pointer;
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

<h2>User Management</h2>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<table>
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Date Created</th>
        <th>Action</th>
    </tr>

    <?php if ($users && $users->num_rows > 0): ?>
        <?php while ($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <?php if ($row['role'] == 'admin'): ?>

                        Admin Account

                    <?php elseif ($row['status'] == 'pending'): ?>

                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="approve">Approve</button>
                        </form>

                    <?php elseif ($row['status'] == 'approved'): ?>

                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="disable">Disable</button>
                        </form>

                    <?php elseif ($row['status'] == 'disabled'): ?>

                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="enable">Enable</button>
                        </form>

                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">No users found.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
