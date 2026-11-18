<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {

    $file = $_FILES["image"];

    $allowed = ["jpg", "jpeg", "png"];
    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed)) {
        $message = "Only JPG, JPEG and PNG images are allowed.";
    } else {

        if (!is_dir("images")) {
            mkdir("images", 0777, true);
        }

        $fileName = time() . "_" . uniqid() . "." . $extension;
        $targetPath = "images/" . $fileName;

        if (move_uploaded_file($file["tmp_name"], $targetPath)) {
            $message = "Image uploaded successfully.";
        } else {
            $message = "Image upload failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Image Privacy Protection</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    text-align: center;
    margin: 0;
    padding: 30px;
}

.container {
    background: white;
    max-width: 950px;
    margin: auto;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

h2 {
    color: #222;
}

input[type="file"] {
    margin: 15px;
}

button {
    padding: 11px 20px;
    margin: 8px;
    border: none;
    border-radius: 6px;
    background: #007bff;
    color: white;
    cursor: pointer;
    font-size: 15px;
}

button:hover {
    background: #0056b3;
}

#canvas {
    max-width: 100%;
    height: auto;
    border: 2px solid #333;
    cursor: crosshair;
    touch-action: none;
    margin-top: 20px;
}

#controls {
    display: none;
}

#message {
    color: green;
    font-weight: bold;
}

#selectionInfo {
    color: #555;
    margin-top: 10px;
}

</style>

</head>

<body>

<div class="container">

<h2>Image Privacy Protection</h2>

<p>
Upload an image, select sensitive information and blur it.
</p>

<form method="POST" enctype="multipart/form-data">

<input
    type="file"
    name="image"
    id="imageInput"
    accept=".jpg,.jpeg,.png"
    required
>

<br>

<button type="submit">Upload Image</button>

</form>

<p id="message">
<?php echo htmlspecialchars($message); ?>
</p>

<canvas id="canvas"></canvas>

<div id="controls">

<p id="selectionInfo">
Drag your mouse over the sensitive area.
</p>

<button type="button" id="blurButton">
Blur Selected Area
</button>

<button type="button" id="saveButton">
Save Protected Image
</button>

</div>

</div>


<script>

const imageInput = document.getElementById("imageInput");
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

const controls = document.getElementById("controls");
const blurButton = document.getElementById("blurButton");
const saveButton = document.getElementById("saveButton");
const selectionInfo = document.getElementById("selectionInfo");

let image = new Image();

let selection = null;

let startX = 0;
let startY = 0;

let isSelecting = false;


/* =========================
   LOAD IMAGE
========================= */

imageInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {

        image.onload = function () {

            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;

            ctx.drawImage(
                image,
                0,
                0,
                canvas.width,
                canvas.height
            );

            controls.style.display = "block";

            selection = null;

            selectionInfo.innerHTML =
                "Drag your mouse over the sensitive area.";

        };

        image.src = event.target.result;

    };

    reader.readAsDataURL(file);

});


/* =========================
   GET REAL CANVAS POSITION
========================= */

function getPosition(event) {

    const rect = canvas.getBoundingClientRect();

    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    return {

        x: (event.clientX - rect.left) * scaleX,

        y: (event.clientY - rect.top) * scaleY

    };

}


/* =========================
   POINTER DOWN
========================= */

canvas.addEventListener("pointerdown", function(event) {

    event.preventDefault();

    canvas.setPointerCapture(event.pointerId);

    const pos = getPosition(event);

    startX = pos.x;
    startY = pos.y;

    isSelecting = true;

    selection = null;

});


/* =========================
   POINTER MOVE
========================= */

canvas.addEventListener("pointermove", function(event) {

    if (!isSelecting) {
        return;
    }

    event.preventDefault();

    const pos = getPosition(event);

    const currentX = pos.x;
    const currentY = pos.y;

    const x = Math.min(startX, currentX);
    const y = Math.min(startY, currentY);

    const width = Math.abs(currentX - startX);
    const height = Math.abs(currentY - startY);

    /*
       Redraw original image
    */

    ctx.drawImage(
        image,
        0,
        0,
        canvas.width,
        canvas.height
    );

    /*
       Draw selection rectangle
    */

    ctx.strokeStyle = "red";
    ctx.lineWidth = 4;

    ctx.strokeRect(
        x,
        y,
        width,
        height
    );

});


/* =========================
   POINTER UP
========================= */

canvas.addEventListener("pointerup", function(event) {

    if (!isSelecting) {
        return;
    }

    event.preventDefault();

    const pos = getPosition(event);

    const x = Math.min(startX, pos.x);
    const y = Math.min(startY, pos.y);

    const width = Math.abs(pos.x - startX);
    const height = Math.abs(pos.y - startY);

    isSelecting = false;

    selection = {
        x: x,
        y: y,
        width: width,
        height: height
    };

    /*
       Draw final red selection
    */

    ctx.drawImage(
        image,
        0,
        0,
        canvas.width,
        canvas.height
    );

    ctx.strokeStyle = "red";
    ctx.lineWidth = 4;

    ctx.strokeRect(
        x,
        y,
        width,
        height
    );

    selectionInfo.innerHTML =
        "Area selected. Click 'Blur Selected Area'.";

});


/* =========================
   POINTER CANCEL
========================= */

canvas.addEventListener("pointercancel", function(event) {

    isSelecting = false;

});


/* =========================
   BLUR SELECTED AREA
========================= */

blurButton.addEventListener("click", function() {

    if (!selection) {

        alert("Please select an area first.");

        return;

    }

    if (
        selection.width < 5 ||
        selection.height < 5
    ) {

        alert("Please select a larger area.");

        return;

    }


    const x = Math.round(selection.x);
    const y = Math.round(selection.y);

    const width = Math.round(selection.width);
    const height = Math.round(selection.height);


    /*
       Get selected image area
    */

    const imageData = ctx.getImageData(
        x,
        y,
        width,
        height
    );


    /*
       Pixelation size
    */

    const pixelSize = 18;


    /*
       Pixelate the selected area
    */

    for (let py = 0; py < height; py += pixelSize) {

        for (let px = 0; px < width; px += pixelSize) {

            const pixelX = px;
            const pixelY = py;

            const index =
                ((pixelY * width) + pixelX) * 4;

            const red = imageData.data[index];
            const green = imageData.data[index + 1];
            const blue = imageData.data[index + 2];

            const blockWidth =
                Math.min(pixelSize, width - px);

            const blockHeight =
                Math.min(pixelSize, height - py);


            for (
                let yy = 0;
                yy < blockHeight;
                yy++
            ) {

                for (
                    let xx = 0;
                    xx < blockWidth;
                    xx++
                ) {

                    const currentIndex =
                        (((pixelY + yy) * width)
                        + (pixelX + xx)) * 4;

                    imageData.data[currentIndex] = red;
                    imageData.data[currentIndex + 1] = green;
                    imageData.data[currentIndex + 2] = blue;

                }

            }

        }

    }


    /*
       Put blurred/pixelated area back
    */

    ctx.putImageData(
        imageData,
        x,
        y
    );


    /*
       Remove red selection rectangle
    */

    selection = null;


    selectionInfo.innerHTML =
        "✓ Selected area has been blurred. You can select another area.";


});


/* =========================
   SAVE PROTECTED IMAGE
========================= */

saveButton.addEventListener("click", function() {

    if (!canvas.width || !canvas.height) {

        alert("Please upload an image first.");

        return;

    }


    /*
       Convert canvas to PNG
    */

    const imageData = canvas.toDataURL("image/png");


    /*
       Create form
    */

    const form = document.createElement("form");

    form.method = "POST";
    form.action = "protect_image.php";


    const input = document.createElement("input");

    input.type = "hidden";
    input.name = "image_data";
    input.value = imageData;


    form.appendChild(input);

    document.body.appendChild(form);

    form.submit();

});

</script>

</body>
</html>