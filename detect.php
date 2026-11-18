<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

$file_id = isset($_GET["file_id"])
    ? intval($_GET["file_id"])
    : 0;

if ($file_id <= 0) {
    die("Invalid file.");
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
    die("Detection is currently available for TXT files only.");
}


/* -------------------------------------------------
   READ FILE
------------------------------------------------- */

$file_path =
    "uploads/" .
    $file["stored_name"];

if (!file_exists($file_path)) {
    die("Original file not found.");
}

$content = file_get_contents($file_path);


/* -------------------------------------------------
   ACTIVITY LOG - PRIVACY SCAN
------------------------------------------------- */

$action = "Privacy scan performed";

$ip_address = $_SERVER["REMOTE_ADDR"];

$log_stmt = $conn->prepare(
    "INSERT INTO activity_logs
    (user_id, file_id, action, ip_address)
    VALUES (?, ?, ?, ?)"
);

$log_stmt->bind_param(
    "iiss",
    $user_id,
    $file_id,
    $action,
    $ip_address
);

$log_stmt->execute();


/* -------------------------------------------------
   DETECT SENSITIVE INFORMATION
------------------------------------------------- */

$detections = [];


/* PASSWORD */

preg_match_all(
    '/(password|passwd|pwd)\s*[:=\-]\s*[^\r\n]+/i',
    $content,
    $matches
);

foreach ($matches[0] as $match) {

    $detections[] = [
        "type" => "Password",
        "text" => trim($match),
        "risk" => "High",
        "description" =>
            "Password or login credential detected."
    ];
}


/* EMAIL */

preg_match_all(
    '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',
    $content,
    $matches
);

foreach ($matches[0] as $match) {

    $detections[] = [
        "type" => "Email",
        "text" => trim($match),
        "risk" => "Medium",
        "description" =>
            "Email address detected."
    ];
}


/* PHONE NUMBER */

preg_match_all(
    '/(?<!\d)(?:\+91[\s-]?)?[6-9]\d{9}(?!\d)/',
    $content,
    $matches
);

foreach ($matches[0] as $match) {

    $detections[] = [
        "type" => "Phone Number",
        "text" => trim($match),
        "risk" => "Medium",
        "description" =>
            "Phone number detected."
    ];
}


/* ACCOUNT NUMBER */

preg_match_all(
    '/(?<!\d)\d{12,18}(?!\d)/',
    $content,
    $matches
);

foreach ($matches[0] as $match) {

    $detections[] = [
        "type" => "Account Number",
        "text" => trim($match),
        "risk" => "High",
        "description" =>
            "Possible account or financial number detected."
    ];
}


/* -------------------------------------------------
   REMOVE DUPLICATES
------------------------------------------------- */

$unique = [];

foreach ($detections as $detection) {

    $key =
        $detection["type"] .
        "|" .
        $detection["text"];

    $unique[$key] = $detection;
}

$detections = array_values($unique);


/* -------------------------------------------------
   SAVE DETECTIONS TO DATABASE
------------------------------------------------- */

foreach ($detections as $detection) {

    $type =
        $detection["type"];

    $text =
        $detection["text"];

    $risk =
        $detection["risk"];

    $check = $conn->prepare(
        "SELECT id
         FROM detections
         WHERE file_id = ?
         AND type = ?
         AND detected_text = ?"
    );

    $check->bind_param(
        "iss",
        $file_id,
        $type,
        $text
    );

    $check->execute();

    $existing =
        $check->get_result();

    if ($existing->num_rows == 0) {

        $insert = $conn->prepare(
            "INSERT INTO detections
            (file_id, type, detected_text, risk_level)
            VALUES (?, ?, ?, ?)"
        );

        $insert->bind_param(
            "isss",
            $file_id,
            $type,
            $text,
            $risk
        );

        $insert->execute();
    }
}


/* -------------------------------------------------
   GET SAVED DETECTIONS
------------------------------------------------- */

$stmt = $conn->prepare(
    "SELECT *
     FROM detections
     WHERE file_id = ?
     ORDER BY id ASC"
);

$stmt->bind_param(
    "i",
    $file_id
);

$stmt->execute();

$saved_detections =
    $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Sensitive Information Detection
</title>


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

    padding: 18px 30px;
}

.navbar h2 {

    margin: 0;
}


/* CONTAINER */

.container {

    width: 90%;

    max-width: 1100px;

    margin: 40px auto;
}


/* CARD */

.card {

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow:
        0 0 10px
        rgba(0,0,0,0.08);
}


/* HEADING */

h1 {

    color: #123b6d;

    margin-top: 0;
}

.file-name {

    color: #555;

    margin-bottom: 25px;
}


/* TABLE */

table {

    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;
}

th {

    background: #123b6d;

    color: white;

    padding: 13px;

    text-align: left;
}

td {

    padding: 13px;

    border-bottom:
        1px solid #ddd;
}


/* CHECKBOX */

.checkbox {

    width: 18px;

    height: 18px;

    cursor: pointer;
}


/* RISK */

.high {

    color: #dc3545;

    font-weight: bold;
}

.medium {

    color: #d97706;

    font-weight: bold;
}


/* PROTECT BUTTON */

.protect-btn {

    margin-top: 25px;

    padding: 13px 22px;

    background: #198754;

    color: white;

    border: none;

    border-radius: 6px;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

.protect-btn:hover {

    background: #146c43;
}


/* BACK BUTTON */

.back {

    display: inline-block;

    margin-top: 20px;

    color: #123b6d;

    text-decoration: none;
}

.back:hover {

    text-decoration: underline;
}


/* WARNING */

.warning {

    background: #fff3cd;

    color: #664d03;

    padding: 15px;

    border-radius: 6px;

    margin-top: 20px;

    border: 1px solid #ffecb5;
}


/* MOBILE */

@media (max-width: 800px) {

    .container {

        width: 95%;
    }

    .card {

        padding: 18px;
    }

    table {

        font-size: 14px;
    }

    th,
    td {

        padding: 9px;
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

</div>


<!-- MAIN CONTAINER -->

<div class="container">


<div class="card">


<h1>
    🔍 Sensitive Information Detection
</h1>


<p class="file-name">

    File:

    <strong>

        <?php

        echo htmlspecialchars(
            $file["original_name"]
        );

        ?>

    </strong>

</p>


<?php if ($saved_detections->num_rows > 0): ?>


<div class="warning">

    ⚠️ Sensitive information was detected.

    Select the information you want to protect.

</div>


<!-- PROTECTION FORM -->

<form
    action="protect.php"
    method="POST"
    onsubmit="return validateSelection();"
>


<input
    type="hidden"
    name="file_id"
    value="<?php echo $file_id; ?>"
>


<table>


<tr>

    <th>
        Select
    </th>

    <th>
        Type
    </th>

    <th>
        Detected Information
    </th>

    <th>
        Risk Level
    </th>

    <th>
        Description
    </th>

</tr>


<?php while (
    $detection =
    $saved_detections->fetch_assoc()
): ?>


<tr>


<!-- CHECKBOX -->

<td>

<input
    type="checkbox"
    class="checkbox"
    name="detection_ids[]"
    value="<?php
        echo $detection["id"];
    ?>"
>


</td>


<!-- TYPE -->

<td>

<?php

echo htmlspecialchars(
    $detection["type"]
);

?>

</td>


<!-- DETECTED INFORMATION -->

<td>

<?php

echo htmlspecialchars(
    $detection["detected_text"]
);

?>

</td>


<!-- RISK -->

<td>

<span
    class="<?php

    echo strtolower(
        $detection["risk_level"]
    );

    ?>"
>

<?php

echo htmlspecialchars(
    $detection["risk_level"]
);

?>

</span>

</td>


<!-- DESCRIPTION -->

<td>

<?php

if (
    $detection["type"]
    === "Password"
) {

    echo
        "Password or login credential detected.";

}
elseif (
    $detection["type"]
    === "Email"
) {

    echo
        "Email address detected.";

}
elseif (
    $detection["type"]
    === "Phone Number"
) {

    echo
        "Phone number detected.";

}
elseif (
    $detection["type"]
    === "Account Number"
) {

    echo
        "Possible account or financial number detected.";

}
else {

    echo
        "Possible sensitive information detected.";

}

?>

</td>


</tr>


<?php endwhile; ?>


</table>


<!-- PROTECT BUTTON -->

<button
    type="submit"
    class="protect-btn"
>

    🛡️ Protect Selected Information

</button>


</form>


<?php else: ?>


<div class="warning">

    ✅ No sensitive information was detected
    in this file.

</div>


<?php endif; ?>


<br>


<a
    href="dashboard.php"
    class="back"
>

    ← Back to Dashboard

</a>


</div>

</div>


<script>

function validateSelection() {

    const checkboxes =
        document.querySelectorAll(
            'input[name="detection_ids[]"]:checked'
        );

    if (checkboxes.length === 0) {

        alert(
            "Please select at least one sensitive information item to protect."
        );

        return false;
    }

    return true;
}

</script>


</body>

</html>