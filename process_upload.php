<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: upload.php");
    exit();
}

if (!isset($_FILES["uploaded_file"])) {
    die("No file was selected.");
}

$file = $_FILES["uploaded_file"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    die("File upload failed.");
}

$max_size = 5 * 1024 * 1024;

if ($file["size"] > $max_size) {
    die("File is too large. Maximum size is 5 MB.");
}

$allowed_extensions = [
    "pdf",
    "jpg",
    "jpeg",
    "png",
    "txt",
    "doc",
    "docx"
];

$original_name = basename($file["name"]);
$extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

if (!in_array($extension, $allowed_extensions)) {
    die("This file type is not allowed.");
}

$upload_directory = "uploads/";

if (!is_dir($upload_directory)) {
    mkdir($upload_directory, 0755, true);
}

$stored_name = bin2hex(random_bytes(16)) . "." . $extension;

$destination = $upload_directory . $stored_name;

if (!move_uploaded_file($file["tmp_name"], $destination)) {
    die("Unable to save the uploaded file.");
}

$user_id = $_SESSION["user_id"];
$file_type = $file["type"];
$file_size = $file["size"];

$stmt = $conn->prepare(
    "INSERT INTO files 
    (user_id, original_name, stored_name, file_type, file_size, status)
    VALUES (?, ?, ?, ?, ?, 'uploaded')"
);

$stmt->bind_param(
    "isssi",
    $user_id,
    $original_name,
    $stored_name,
    $file_type,
    $file_size
);

if ($stmt->execute()) {

    $file_id = $stmt->insert_id;

    $ip_address = $_SERVER["REMOTE_ADDR"];

    $log = $conn->prepare(
        "INSERT INTO activity_logs
        (user_id, file_id, action, ip_address)
        VALUES (?, ?, 'File Uploaded', ?)"
    );

    $log->bind_param(
        "iis",
        $user_id,
        $file_id,
        $ip_address
    );

    $log->execute();

    $log->close();

    echo "<h2>File uploaded successfully!</h2>";
    echo "<p>Original file: " . htmlspecialchars($original_name) . "</p>";
    echo "<p>Your file has been securely stored.</p>";
    echo '<p><a href="dashboard.php">Back to Dashboard</a></p>';

} else {

    if (file_exists($destination)) {
        unlink($destination);
    }

    echo "Database error. File could not be registered.";
}

$stmt->close();

$conn->close();

?>