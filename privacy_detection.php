<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

/* Get user's uploaded files */

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

    <title>Privacy Detection</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
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
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }

        h1 {
            color: #123b6d;
            margin-top: 0;
        }

        .description {
            color: #666;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .scan-btn {
            display: inline-block;
            background: #123b6d;
            color: white;
            padding: 9px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .scan-btn:hover {
            background: #0b294d;
        }

        .status {
            font-weight: bold;
        }

        .protected {
            color: #198754;
        }

        .uploaded {
            color: #d97706;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .upload-btn {
            display: inline-block;
            margin-top: 15px;
            background: #198754;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 5px;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            color: #123b6d;
            text-decoration: none;
        }

        @media (max-width: 800px) {

            .container {
                width: 95%;
            }

            .card {
                overflow-x: auto;
            }

            table {
                min-width: 750px;
            }

        }

    </style>

</head>

<body>


<div class="navbar">

    <h2>Privacy Protection System</h2>

</div>


<div class="container">

    <div class="card">

        <h1>
            🔍 Privacy Detection
        </h1>

        <p class="description">
            Select a file to scan for sensitive information
            such as email addresses, phone numbers,
            passwords and account numbers.
        </p>


        <?php if ($files->num_rows > 0): ?>

        <table>

            <tr>

                <th>File Name</th>

                <th>File Type</th>

                <th>Status</th>

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
                        strtoupper(
                            $file["file_type"]
                        )
                    );
                    ?>
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

                    <a
                        href="detect.php?file_id=<?php echo $file["id"]; ?>"
                        class="scan-btn"
                    >
                        🔍 Scan File
                    </a>

                </td>

            </tr>

            <?php endwhile; ?>

        </table>


        <?php else: ?>

        <div class="empty">

            <h3>No files available for scanning.</h3>

            <p>
                Upload a file first to start privacy detection.
            </p>

            <a
                href="upload.php"
                class="upload-btn"
            >
                📤 Upload File
            </a>

        </div>

        <?php endif; ?>


        <a
            href="dashboard.php"
            class="back"
        >
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>