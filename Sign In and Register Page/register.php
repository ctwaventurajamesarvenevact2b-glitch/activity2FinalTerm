
<?php
include "database.php";

$message = "";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {

        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            $message = "Email already exists!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn,
                "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param($stmt, "sss", $fullname, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php");
                exit();
            } else {
                $message = "Registration failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Register Account</h2>

<form method="POST">

    <label>Full Name:</label><br>
    <input type="text" name="fullname" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit" name="register">Register</button>

</form>

<p><?php echo htmlspecialchars($message); ?></p>

<p>Already have an account? <a href="login.php">Sign In</a></p>

</body>
</html>
