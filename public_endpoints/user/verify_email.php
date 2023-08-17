<?php
session_start();
if (!isset($_SESSION["alert"])) $_SESSION["alert"] = "";

require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");
$engine = new UserEngine(false);
$error = "";

try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        $error = "This endpoint requires GET.";
    } elseif (empty($_GET["email"]) || empty($_GET["key"])) {
        $error = "Missing parameters in the request.";
    } elseif (!$engine->validate_email($_GET["email"], $_GET["key"])) {
        $error = "This validation key is expired or invalid.";
    }
} catch (Exception $ex) {
    // This is to prevent JSON-printing of errors
    $error = "Internal failure.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MajestiCloud email validation</title>
    <link rel="icon" type="image/x-icon" href="/logo.png">
    <link href="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.3.1-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.x-custom/css/bootstrap-qp_custom-colors.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://assets.lesmajesticiels.org/fonts/poppins/import.css" rel="stylesheet">
    <style>
        * {
            font-family: Poppins, system-ui;
        }

        body {
            background-image: linear-gradient(to top right, #f5fcff, #fff);
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-image: linear-gradient(to top right, #020202, #000);
            }
        }
    </style>
</head>

<body style="min-height: 100vh;" class="p-3 d-flex flex-column justify-content-center align-items-center">
    <div class="mb-3">
        <img src="/logo.png" alt="MajestiCloud logo" height="96">
    </div>

    <div class="border rounded-3 shadow p-4 bg-body-tertiary" style="width:100%; max-width: 700px;">
        <?php if (!empty($error)) : ?>
            <h2>Unable to continue</h2>
            <p><?= trim($error) ?></p>
        <?php else : ?>
            <h2>Request successful</h2>
            <p>Your email address has been successfully validated. You can safely close this page.</p>
        <?php endif; ?>
    </div>
    <footer class="m-5">
        &copy; 2014-<?= date("Y") ?> Quentin Pugeat
    </footer>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.3.1-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.x-custom/color-modes-toggler.js"></script>
</body>

</html>