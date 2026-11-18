<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

if (!isset($_GET["file_id"])) {
    die("File ID is missing.");
}

$file_id = intval($_GET["file_id"]);

if ($file_id <= 0) {
    die("Invalid file.");
}


/* -------------------------------------------------
   GET FILE BELONGING TO CURRENT USER
------------------------------------------------- */

$stmt = $conn->prepare(
    "SELECT *
     FROM files
     WHERE id = ?
     AND user_id = ?"
);

$stmt->bind_param(
    "ii",
    $file_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die(
        "Access Denied. You are not allowed to access this file."
    );
}

$file = $result->fetch_assoc();


/* -------------------------------------------------
   CHECK PROTECTION STATUS
------------------------------------------------- */

if ($file["status"] !== "protected") {
    die(
        "This file has not been protected yet."
    );
}


/* -------------------------------------------------
   CHECK PROTECTED FILE NAME
------------------------------------------------- */

if (
    empty($file["protected_name"])
) {
    die(
        "Protected file information is missing."
    );
}


/* -------------------------------------------------
   CREATE PROTECTED FILE PATH
------------------------------------------------- */

$protected_folder =
    "protected_files/";

$protected_file =
    $protected_folder .
    basename(
        $file["protected_name"]
    );


/* -------------------------------------------------
   CHECK FILE EXISTS
------------------------------------------------- */

if (
    !file_exists($protected_file)
) {
    die(
        "Protected file does not exist."
    );
}


/* -------------------------------------------------
   ACTIVITY LOG
------------------------------------------------- */

$action =
    "Protected file downloaded";

$ip_address =
    $_SERVER["REMOTE_ADDR"];

$stmt = $conn->prepare(
    "INSERT INTO activity_logs
    (user_id, file_id, action, ip_address)
    VALUES (?, ?, ?, ?)"
);

$stmt->bind_param(
    "iiss",
    $user_id,
    $file_id,
    $action,
    $ip_address
);

$stmt->execute();


/* -------------------------------------------------
   DOWNLOAD
------------------------------------------------- */

$download_name =
    "protected_" .
    basename(
        $file["original_name"]
    );


header(
    "Content-Type: text/plain"
);

header(
    "Content-Disposition: attachment; filename=\"" .
    $download_name .
    "\""
);

header(
    "Content-Length: " .
    filesize($protected_file)
);

readfile(
    $protected_file
);

exit();

?>