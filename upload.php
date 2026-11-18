<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Upload File - Privacy Protection System</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .navbar {
            background: #1f4e79;
            color: white;
            padding: 15px 30px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            float: right;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #1f4e79;
        }

        input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            padding: 12px 25px;
            background: #1f4e79;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #163a5c;
        }

        .info {
            background: #e8f1f8;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

    </style>

</head>

<body>

<div class="navbar">

    <strong>Privacy Protection System</strong>

    <a href="dashboard.php">Dashboard</a>

</div>


<div class="container">

    <div class="box">

        <h2>Upload File</h2>

        <p>
            Upload an image or document to check for sensitive information.
        </p>


        <form action="process_upload.php" method="POST" enctype="multipart/form-data">

            <input
                type="file"
                name="uploaded_file"
                required
            >


            <button type="submit">
                Upload & Scan
            </button>

        </form>


        <div class="info">

            <strong>Supported files:</strong>

            <p>
                PDF, JPG, JPEG, PNG, TXT, DOC, DOCX
            </p>

            <p>
                Maximum file size: 5 MB
            </p>

        </div>

    </div>

</div>

</body>

</html>