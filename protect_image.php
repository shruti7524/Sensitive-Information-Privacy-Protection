<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: image_protection.php");
    exit();
}

if (!isset($_POST["image_data"]) || empty($_POST["image_data"])) {
    die("No protected image data received.");
}

$imageData = $_POST["image_data"];

/*
    Remove the base64 image prefix
*/
$imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);

/*
    Decode image
*/
$imageData = base64_decode($imageData);

if ($imageData === false) {
    die("Invalid image data.");
}

/*
    Make sure protected/images folder exists
*/
$folder = "protected/images/";

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

/*
    Create unique protected image name
*/
$fileName = "protected_" . time() . "_" . uniqid() . ".png";

$filePath = $folder . $fileName;

/*
    Save protected image
*/
if (file_put_contents($filePath, $imageData) === false) {
    die("Unable to save protected image.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Protected Image</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            text-align: center;
            padding: 30px;
        }

        .container {
            background: white;
            max-width: 800px;
            margin: auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        img {
            max-width: 100%;
            max-height: 600px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            margin: 8px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            background: #007bff;
        }

        .btn:hover {
            background: #0056b3;
        }

        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Protected Image</h2>

    <p class="success">
        ✓ Sensitive area protected successfully!
    </p>

    <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Protected Image">

    <br>

    <a class="btn"
       href="<?php echo htmlspecialchars($filePath); ?>"
       download>
        Download Protected Image
    </a>

    <a class="btn"
       href="image_protection.php">
        Protect Another Image
    </a>

</div>

</body>
</html>