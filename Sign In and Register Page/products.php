
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
$edit = null;

// ADD PRODUCT
if (isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $desc = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $qty = (int) $_POST['quantity'];

    if ($name == "" || $price < 0 || $qty < 0) {
        $message = "Please enter valid product details.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $name, $desc, $price, $qty);
        $stmt->execute();
        header("Location: products.php?success=added");
        exit();
    }
}

// UPDATE PRODUCT
if (isset($_POST['update_product'])) {
    $id = (int) $_POST['product_id'];
    $name = trim($_POST['product_name']);
    $desc = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $qty = (int) $_POST['quantity'];

    if ($name == "" || $price < 0 || $qty < 0) {
        $message = "Please enter valid product details.";
    } else {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, description=?, price=?, quantity=? WHERE id=?");
        $stmt->bind_param("ssdii", $name, $desc, $price, $qty, $id);
        $stmt->execute();
        header("Location: products.php?success=updated");
        exit();
    }
}

// DELETE PRODUCT
if (isset($_POST['delete_product'])) {
    $id = (int) $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: products.php?success=deleted");
    exit();
}

// EDIT PRODUCT
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

// SUCCESS MESSAGE
$messages = [
    "added" => "Product added successfully!",
    "updated" => "Product updated successfully!",
    "deleted" => "Product deleted successfully!"
];

if (isset($_GET['success'])) {
    $message = $messages[$_GET['success']] ?? "";
}

// PRODUCT LIST
$products = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>

    <style>
        * {
            box-sizing: border-box;
        }

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

        nav a:hover {
            text-decoration: underline;
        }

        h3 {
            margin-bottom: 30px;
        }

        .form-container {
            display: flex;
            gap: 20px;
            max-width: 450px;
        }

        .left-form {
            flex: 1;
        }

        .right-form {
            width: 125px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input, textarea {
            width: 100%;
            padding: 6px;
            margin-bottom: 8px;
            font-family: inherit;
        }

        textarea {
            height: 65px;
        }

        button {
            cursor: pointer;
            padding: 4px 10px;
            font-family: inherit;
        }

        .submit-btn {
            width: 100%;
            margin-top: 15px;
        }

        hr {
            margin: 30px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }

        h2 {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #d58b9b;
            font-weight: normal;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 7px;
            text-align: center;
        }

        .action-btn {
            color: black;
            margin: 0 5px;
        }

        .message {
            color: green;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            table {
                font-size: 11px;
            }

            th, td {
                padding: 4px;
            }
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

<h3><?= $edit ? "Edit Product" : "Add Product" ?></h3>

<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" action="products.php">

    <?php if ($edit): ?>
        <input type="hidden" name="product_id" value="<?= $edit['id'] ?>">
    <?php endif; ?>

    <div class="form-container">

        <div class="left-form">
            <label>Product Name:</label>
            <input type="text" name="product_name" required
                value="<?= htmlspecialchars($edit['product_name'] ?? '') ?>">

            <label>Description:</label>
            <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="right-form">
            <label>Quantity:</label>
            <input type="number" name="quantity" min="0" required
                value="<?= htmlspecialchars($edit['quantity'] ?? '') ?>">

            <label>Price:</label>
            <input type="number" name="price" step="0.01" min="0" required
                value="<?= htmlspecialchars($edit['price'] ?? '') ?>">

            <?php if ($edit): ?>
                <button type="submit" name="update_product" class="submit-btn">Update Product</button>
                <a href="products.php">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_product" class="submit-btn">Add Product</button>
            <?php endif; ?>
        </div>

    </div>
</form>

<hr>

<h2>Product List</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Date Saved</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($products && $products->num_rows > 0): ?>
            <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format((float)$row['price'], 2) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td>
                        <a class="action-btn" href="products.php?edit=<?= $row['id'] ?>">Edit</a>

                        <form method="POST" action="products.php"
                            style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this product?');">

                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_product">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No products found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
