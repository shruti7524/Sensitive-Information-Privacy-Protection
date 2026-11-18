<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION["user_name"];

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Privacy Protection System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        /* NAVBAR */

        .navbar {
            background: #123b6d;
            color: white;
            padding: 18px 40px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .logout {
            background: #dc3545;
            color: white;

            padding: 10px 18px;

            text-decoration: none;

            border-radius: 5px;
        }

        .logout:hover {
            background: #b02a37;
        }

        /* MAIN CONTAINER */

        .container {
            width: 90%;
            max-width: 1100px;

            margin: 40px auto;
        }

        /* WELCOME */

        .welcome {
            background: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 30px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }

        .welcome h1 {
            margin-top: 0;

            color: #123b6d;
        }

        .welcome p {
            color: #555;

            font-size: 16px;
        }

        /* CARDS */

        .cards {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 25px;
        }

        .card {
            display: block;

            background: white;

            padding: 30px;

            border-radius: 10px;

            text-decoration: none;

            color: inherit;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);

            transition: 0.2s;

            cursor: pointer;
        }

        .card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 5px 18px rgba(0,0,0,0.15);
        }

        .card h2 {

            color: #123b6d;

            margin-top: 0;

            margin-bottom: 12px;
        }

        .card p {

            color: #666;

            line-height: 1.5;

            min-height: 48px;
        }

        /* CARD BUTTON */

        .card-button {

            display: inline-block;

            margin-top: 15px;

            background: #123b6d;

            color: white;

            padding: 10px 18px;

            border-radius: 5px;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .cards {

                grid-template-columns:
                    1fr;
            }

            .navbar {

                padding:
                    15px 20px;
            }

            .navbar h2 {

                font-size:
                    18px;
            }

            .container {

                width:
                    94%;
            }

        }

    </style>

</head>

<body>

    <!-- NAVBAR -->

    <div class="navbar">

        <h2>
            Privacy Protection System
        </h2>

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

    <!-- MAIN CONTAINER -->

    <div class="container">

        <!-- WELCOME SECTION -->

        <div class="welcome">

            <h1>

                Welcome,
                <?php
                echo htmlspecialchars(
                    $user_name
                );
                ?>!

            </h1>

            <p>

                Manage your files and protect
                sensitive information from
                accidental exposure.

            </p>

        </div>

        <!-- DASHBOARD CARDS -->

        <div class="cards">

            <!-- UPLOAD FILE -->

            <a
                href="upload.php"
                class="card"
            >

                <h2>
                    📤 Upload File
                </h2>

                <p>

                    Upload a document or
                    text file to scan it
                    for sensitive information.

                </p>

                <span class="card-button">

                    Upload File

                </span>

            </a>

            <!-- MY FILES -->

            <a
                href="my_files.php"
                class="card"
            >

                <h2>
                    📁 My Files
                </h2>

                <p>

                    View and manage all
                    files uploaded to
                    your account.

                </p>

                <span class="card-button">

                    View My Files

                </span>

            </a>

            <!-- PRIVACY DETECTION -->

            <a
                href="privacy_detection.php"
                class="card"
            >

                <h2>
                    🔍 Privacy Detection
                </h2>

                <p>

                    Scan your files for
                    sensitive information
                    such as emails, phone
                    numbers and passwords.

                </p>

                <span class="card-button">

                    Scan Files

                </span>

            </a>

            <!-- ACTIVITY LOGS -->

            <a
                href="activity_logs.php"
                class="card"
            >

                <h2>
                    📋 Activity Logs
                </h2>

                <p>

                    View the history of
                    activities performed
                    on your uploaded files.

                </p>

                <span class="card-button">

                    View Activity Logs

                </span>

            </a>

            <!-- IMAGE PROTECTION -->

            <a
                href="image_protection.php"
                class="card"
            >

                <h2>
                    🖼️ Image Protection
                </h2>

                <p>

                    Upload images and protect
                    sensitive information such as
                    certificate details, ID numbers,
                    vehicle numbers and QR codes.

                </p>

                <span class="card-button">

                    Protect Image

                </span>

            </a>

        </div>

    </div>

</body>

</html>