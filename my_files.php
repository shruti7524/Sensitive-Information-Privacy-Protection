<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

/* Get files uploaded by the logged-in user */

$stmt = $conn->prepare(
    "SELECT *
     FROM files
     WHERE user_id = ?
     ORDER BY uploaded_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$files = $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

    <title>My Files</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #123b6d;
            color: white;
            padding: 18px 30px;
        }

        .navbar h2 {
            margin: 0;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }

        h1 {
            color: #123b6d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th {
            background: #123b6d;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
        }

        .status {
            font-weight: bold;
        }

        .uploaded {
            color: #d97706;
        }

        .protected {
            color: #198754;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            background: #123b6d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background: #0b294d;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            color: #123b6d;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }

    </style>

</head>

<body>

<div class="navbar">

    <h2>Privacy Protection System</h2>

</div>

<div class="container">

    <div class="card">

        <h1>My Files</h1>

        <p>
            View and manage the files uploaded to your account.
        </p>

        <?php if ($files->num_rows > 0): ?>

        <table>

            <tr>

                <th>File Name</th>

                <th>Type</th>

                <th>Size</th>

                <th>Status</th>

                <th>Uploaded</th>

                <th>Action</th>

            </tr>

            <?php while ($file = $files->fetch_assoc()): ?>

            <tr>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $file["original_name"]
                    );
                    ?>
                </td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        strtoupper($file["file_type"])
                    );
                    ?>
                </td>

                <td>
                    <?php
                    echo number_format(
                        $file["file_size"] / 1024,
                        2
                    );
                    ?>
                    KB
                </td>

                <td>

                    <?php if ($file["status"] === "protected"): ?>

                        <span class="status protected">
                            Protected
                        </span>

                    <?php else: ?>

                        <span class="status uploaded">
                            Uploaded
                        </span>

                    <?php endif; ?>

                </td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $file["uploaded_at"]
                    );
                    ?>
                </td>

                <td>

                    <a
                        class="btn"
                        href="detect.php?file_id=<?php echo $file["id"]; ?>"
                    >
                        Detect
                    </a>

                </td>

            </tr>

            <?php endwhile; ?>

        </table>

        <?php else: ?>

            <div class="empty">

                <h3>No files uploaded yet.</h3>

                <p>
                    Upload a file to start detecting sensitive information.
                </p>

                <a
                    class="btn"
                    href="upload.php"
                >
                    Upload File
                </a>

            </div>

        <?php endif; ?>

        <a
            class="back"
            href="dashboard.php"
        >
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>