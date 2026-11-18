<?php

require_once "includes/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "error";

    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $message_type = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Privacy Protection System</title>

    <style>

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .container {
            width: 350px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #1f4e79;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background: #1f4e79;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #163a5c;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            border-radius: 5px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .login {
            text-align: center;
            margin-top: 15px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Create Account</h2>

    <?php if (!empty($message)): ?>

        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Full Name</label>

        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            required
        >


        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Create a password"
            required
        >


        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm_password"
            placeholder="Confirm your password"
            required
        >


        <button type="submit">
            Register
        </button>

    </form>


    <div class="login">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>

</div>

</body>

</html>