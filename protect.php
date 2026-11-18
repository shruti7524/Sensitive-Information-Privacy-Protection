<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$user_id = $_SESSION["user_id"];

$file_id = isset($_POST["file_id"])
    ? intval($_POST["file_id"])
    : 0;

$detection_ids = isset($_POST["detection_ids"])
    ? $_POST["detection_ids"]
    : [];

if ($file_id <= 0) {
    die("Invalid file.");
}

if (empty($detection_ids)) {
    die("Please select at least one item to protect.");
}


/* -------------------------------------------------
   GET FILE AND CHECK OWNERSHIP
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
    die("File not found or access denied.");
}

$file = $result->fetch_assoc();


/* -------------------------------------------------
   CHECK FILE TYPE
------------------------------------------------- */

$extension = strtolower(
    pathinfo(
        $file["original_name"],
        PATHINFO_EXTENSION
    )
);

if ($extension !== "txt") {
    die("Protection is currently available for TXT files only.");
}


/* -------------------------------------------------
   ORIGINAL FILE
------------------------------------------------- */

$file_path =
    "uploads/" .
    $file["stored_name"];

if (!file_exists($file_path)) {
    die("Original file not found.");
}

$content = file_get_contents($file_path);


/* -------------------------------------------------
   GET SELECTED DETECTIONS
------------------------------------------------- */

$placeholders = implode(
    ",",
    array_fill(
        0,
        count($detection_ids),
        "?"
    )
);

$types = str_repeat(
    "i",
    count($detection_ids)
);

$sql =
    "SELECT *
     FROM detections
     WHERE file_id = ?
     AND id IN ($placeholders)";

$stmt = $conn->prepare($sql);

$params = array_merge(
    [$file_id],
    array_map(
        "intval",
        $detection_ids
    )
);

$bind_types =
    "i" . $types;

$stmt->bind_param(
    $bind_types,
    ...$params
);

$stmt->execute();

$detections =
    $stmt->get_result();


/* -------------------------------------------------
   REDACT SENSITIVE INFORMATION
------------------------------------------------- */

while (
    $detection =
    $detections->fetch_assoc()
) {

    $sensitive_text =
        $detection["detected_text"];

    $type =
        $detection["type"];

    if ($sensitive_text === "") {
        continue;
    }


    /* EMAIL */

    if ($type === "Email") {

        $parts =
            explode(
                "@",
                $sensitive_text,
                2
            );

        if (count($parts) === 2) {

            $replacement =
                str_repeat(
                    "*",
                    strlen($parts[0])
                )
                . "@"
                . $parts[1];

        } else {

            $replacement =
                str_repeat(
                    "*",
                    strlen($sensitive_text)
                );
        }
    }


    /* PHONE */

    elseif (
        $type === "Phone Number"
    ) {

        $length =
            strlen($sensitive_text);

        if ($length > 4) {

            $replacement =
                str_repeat(
                    "*",
                    $length - 4
                )
                . substr(
                    $sensitive_text,
                    -4
                );

        } else {

            $replacement =
                str_repeat(
                    "*",
                    $length
                );
        }
    }


    /* PASSWORD */

    elseif (
        $type === "Password"
    ) {

        if (
            preg_match(
                '/^(password|passwd|pwd)\s*[:=\-]\s*(.+)$/i',
                $sensitive_text,
                $matches
            )
        ) {

            $label =
                $matches[1];

            $password_value =
                $matches[2];

            $replacement =
                $label
                . "-"
                . str_repeat(
                    "*",
                    strlen(
                        $password_value
                    )
                );

        } else {

            $replacement =
                str_repeat(
                    "*",
                    strlen(
                        $sensitive_text
                    )
                );
        }
    }


    /* OTHER SENSITIVE INFORMATION */

    else {

        $replacement =
            str_repeat(
                "*",
                strlen(
                    $sensitive_text
                )
            );
    }


    /* REPLACE TEXT */

    $content =
        str_replace(
            $sensitive_text,
            $replacement,
            $content
        );
}


/* -------------------------------------------------
   CREATE PROTECTED FILE
------------------------------------------------- */

$protected_name =
    "protected_" .
    bin2hex(
        random_bytes(16)
    ) .
    ".txt";

$protected_path =
    "protected_files/" .
    $protected_name;


if (
    file_put_contents(
        $protected_path,
        $content
    ) === false
) {

    die(
        "Unable to create protected file."
    );
}


/* -------------------------------------------------
   SAVE PROTECTED FILE NAME
   FOR THIS SPECIFIC FILE
------------------------------------------------- */

$stmt = $conn->prepare(
    "UPDATE files
     SET status = 'protected',
         protected_name = ?
     WHERE id = ?
     AND user_id = ?"
);

$stmt->bind_param(
    "sii",
    $protected_name,
    $file_id,
    $user_id
);

$stmt->execute();


/* -------------------------------------------------
   ACTIVITY LOG
------------------------------------------------- */

$action =
    "Sensitive information redacted";

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

?>

<!DOCTYPE html>

<html>

<head>

<title>File Protected</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    text-align: center;
    padding-top: 80px;
}

.box {
    background: white;
    width: 550px;
    max-width: 90%;
    margin: auto;
    padding: 40px;
    border-radius: 12px;
    box-shadow:
        0 0 15px
        rgba(0,0,0,0.12);
}

h1 {
    color: #123b6d;
    margin-bottom: 15px;
}

p {
    color: #555;
    font-size: 16px;
}

.download-btn {
    display: inline-block;
    margin-top: 25px;
    padding: 15px 28px;
    background: #198754;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: bold;
}

.download-btn:hover {
    background: #146c43;
}

.back-btn {
    display: inline-block;
    margin-top: 25px;
    text-decoration: none;
    color: #123b6d;
}

</style>

</head>

<body>

<div class="box">

    <h1>
        ✅ File Protected Successfully
    </h1>

    <p>
        Your selected sensitive information
        has been protected.
    </p>

    <p>
        Your protected file is ready
        to download.
    </p>


    <a
        href="download.php?file_id=<?php echo $file_id; ?>"
        class="download-btn"
    >
        ⬇ Download Protected File
    </a>

    <br>

    <a
        href="dashboard.php"
        class="back-btn"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>