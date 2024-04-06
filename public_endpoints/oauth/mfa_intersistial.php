<?php
session_start();
if (!isset($_SESSION["alert"])) $_SESSION["alert"] = "";

require_once(__DIR__ . "/../../engine/oauth/OAuthEngine.class.php");
$engine = new OAuthEngine();
$error = "";

try {
    // Check for mendatory values
    if (empty($_REQUEST["code"]) || empty($_REQUEST["client_uuid"])) {
        $error .= " Missing parameters in the request.";
    }
    $client = $engine->select_client($_REQUEST["client_uuid"]);

    // If the form is POSTed
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["totp"])) {
        $check = $engine->check_totp($_REQUEST["code"], $_REQUEST["client_uuid"], $_REQUEST["totp"]);

        if (!$check) {
            $_SESSION["alert"] = "Invalid OTP code. Maybe it expired, or your OTP app is misconfigured.";
        } else {
            http_response_code(307);
            header("Location: " . $client["callback_url"] . "?code=" . urlencode($_REQUEST["code"]));
        }
    }
} catch (Exception $ex) {
    // This is to prevent JSON-printing of errors
    if ((new Environment())->item("ENVIRONMENT_TYPE") == "development") {
        $error .= $ex->__toString();
    } else {
        $error .= " Internal failure.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication validation intersistial | MajestiCloud</title>
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
            <p>Your client is probably misconfigured. Please check the request validity and try again.</p>
            <pre><?= trim($error) ?></pre>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["totp"]) && empty($_SESSION["alert"])) : ?>
            <p>Please wait...</p>
        <?php else : ?>
            <h2>One-Time Password required</h2>
            <p>This account is configured to require multi-factor authentication. Please open your OTP app and copy here the code displayed for MajestiCloud.</p>

            <?php if (!empty($_SESSION["alert"])) : ?>
                <div class="alert alert-info">
                    <?= $_SESSION["alert"] ?>
                </div>
                <?php $_SESSION["alert"] = ""; ?>
            <?php endif; ?>

            <form action="mfa_intersistial.php" method="POST">
                <input type="hidden" name="code" value="<?= htmlspecialchars($_REQUEST["code"], ENT_COMPAT) ?>">
                <input type="hidden" name="client_uuid" value="<?= htmlspecialchars($_REQUEST["client_uuid"], ENT_COMPAT) ?>">

                <div class="mb-3">
                    <label for="totpInput" class="form-label">Code</label>
                    <input type="text" class="form-control" id="totpInput" name="totp" inputmode="numeric" autocomplete="one-time-code" minlength="6" required>
                    <div class="invalid-feedback">Please type the six-digits code given by your OTP app.</div>
                </div>
                <div>
                    <button type="submit" id="submitBtn" class="btn btn-primary shadow-sm">Continue <i class="bi bi-chevron-right"></i></button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <footer class="m-5">
        &copy; 2014-<?= date("Y") ?> Quentin Pugeat
    </footer>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.3.1-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.x-custom/color-modes-toggler.js"></script>
</body>

</html>