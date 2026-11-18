<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

/* Get activity logs of logged-in user */

$stmt = $conn->prepare(
    "SELECT 
        activity_logs.*,
        files.original_name
     FROM activity_logs
     LEFT JOIN files
        ON activity_logs.file_id = files.id
     WHERE activity_logs.user_id = ?
     ORDER BY activity_logs.created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$logs = $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

    <title>Activity Logs</title>

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

        .action {
            font-weight: bold;
            color: #123b6d;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            color: #123b6d;
        }

        .back:hover {
            text-decoration: underline;
        }

        @media (max-width: 800px) {

            .container {
                width: 95%;
            }

            .card {
                padding: 15px;
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

        <h1>Activity Logs</h1>

        <p class="description">
            View the activities performed on your files.
        </p>


        <?php if ($logs->num_rows > 0): ?>

        <table>

            <tr>

                <th>Action</th>

                <th>File Name</th>

                <th>IP Address</th>

                <th>Date & Time</th>

            </tr>


            <?php while ($log = $logs->fetch_assoc()): ?>

            <tr>

                <td class="action">
                    <?php
                    echo htmlspecialchars(
                        $log["action"]
                    );
                    ?>
                </td>

                <td>

                    <?php

                    if (!empty($log["original_name"])) {

                        echo htmlspecialchars(
                            $log["original_name"]
                        );

                    } else {

                        echo "-";

                    }

                    ?>

                </td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $log["ip_address"]
                    );
                    ?>
                </td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $log["created_at"]
                    );
                    ?>
                </td>

            </tr>

            <?php endwhile; ?>

        </table>


        <?php else: ?>

        <div class="empty">

            <h3>No activity recorded yet.</h3>

            <p>
                Your file activities will appear here.
            </p>

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