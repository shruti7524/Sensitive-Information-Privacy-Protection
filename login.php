<?php

session_start();

require_once "includes/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Invalid email or password.";
                $message_type = "error";
            }

        } else {

            $message = "Invalid email or password.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Privacy Protection System</title>

    <style>

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .container {
            width: 350px;
            margin: 100px auto;
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

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .register {
            text-align: center;
            margin-top: 15px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Privacy Protection System</h2>

    <?php if (!empty($message)): ?>

        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

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
            placeholder="Enter your password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <div class="register">

        Don't have an account?

        <a href="register.php">
            Register
        </a>

    </div>

</div>

</body>

</html>