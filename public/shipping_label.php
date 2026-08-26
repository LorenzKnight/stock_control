<?php
$labelsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/css/labels.css');
$shippingLabelVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/shippingLabel.js');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="images/sys-img/asc-favicon.png" />
    <title>Shipping Label</title>
    <link rel="stylesheet" href="css/labels.css?v=<?= $labelsVersion ?>">
    <script defer src="js/shippingLabel.js?v=<?= $shippingLabelVersion ?>"></script>
</head>

<body>
    <div class="label" id="shippingLabel">
        <p>Loading shipping label...</p>
    </div>
</body>
</html>