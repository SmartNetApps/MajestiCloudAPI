<?php
session_start();
if(!isset($_SESSION["alert"])) $_SESSION["alert"] = "";

require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $engine = new UserEngine(false);
    if (empty($_POST["username"]) | empty($_POST["password"]) | empty($_POST["display_name"])) {
        $_SESSION["alert"] .= " All fields are required.";
    } else {
        if ($engine->does_user_exist($_POST["username"])) {
            $_SESSION["alert"] .= " A user with this primary email is already registered.";
        } else {
            $uuid = $engine->create_user($_POST["username"], $_POST["password"], htmlspecialchars($_POST["display_name"]));
            $_SESSION["alert"] .= " Successfully created the user.";

            if(empty($_REQUEST['client_uuid']) || empty($_REQUEST['redirect_uri'])) {
                $_SESSION["alert"] .= " You can now go back to your client and log into it through MajestiCloud.";
            } else {
                $_SESSION["alert"] .= " You can now log into MajestiCloud.";
                header("Location: authorize.php?".http_build_query([
                    "client_uuid" => $_REQUEST["client_uuid"],
                    "redirect_uri" => $_REQUEST["redirect_uri"],
                    "code_challenge" => !empty($_REQUEST["code_challenge"]) ? $_REQUEST["code_challenge"] : "",
                    "code_challenge_method" => !empty($_REQUEST["code_challenge_method"]) ? $_REQUEST["code_challenge_method"] : "",
                    "username" => $_REQUEST["username"]
                ]));
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account for MajestiCloud</title>
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
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_SESSION["alert"])) : ?>
            <p>Please wait...</p>
        <?php else : ?>
            <h2>Create an account for MajestiCloud</h2>
            <?php if (!empty($_SESSION["alert"])) : ?>
                <div class="alert alert-info">
                    <?= $_SESSION["alert"] ?>
                </div>
                <?php $_SESSION["alert"] = ""; ?>
            <?php endif; ?>
            <form action="newaccount.php" method="POST">
                <?php if (!empty($_REQUEST['client_uuid']) && !empty($_REQUEST['redirect_uri'])) : ?>
                    <input type="hidden" name="client_uuid" value="<?= $_REQUEST['client_uuid'] ?>">
                    <input type="hidden" name="redirect_uri" value="<?= $_REQUEST['redirect_uri'] ?>">
                    <?php if (!empty($_REQUEST["code_challenge"]) && !empty($_REQUEST["code_challenge_method"])) : ?>
                        <input type="hidden" name="code_challenge" value="<?= $_REQUEST['code_challenge'] ?>">
                        <input type="hidden" name="code_challenge_method" value="<?= $_REQUEST['code_challenge_method'] ?>">
                    <?php endif; ?>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="emailInput" class="form-label">Name or nickname</label>
                    <input type="text" class="form-control" id="nameInput" placeholder="" name="display_name" value="<?php if (isset($_REQUEST["display_name"])) echo $_REQUEST["display_name"]; ?>" autocomplete="name" required>
                    <div class="invalid-feedback">Enter your full name or any nickname.</div>
                </div>
                <div class="mb-3">
                    <label for="emailInput" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="emailInput" placeholder="name@example.com" name="username" value="<?php if (isset($_REQUEST["username"])) echo $_REQUEST["username"]; ?>" autocomplete="username" required>
                    <div class="invalid-feedback">Please type your account's primary email address.</div>
                </div>
                <div class="mb-3">
                    <label for="pwdInput" class="form-label">Password</label>
                    <input type="password" class="form-control" id="pwdInput" placeholder="YourPassword" name="password" autocomplete="current-password" required>
                    <div class="invalid-feedback">Please type your account's password.</div>
                </div>
                <div>
                    <button type="submit" id="submitBtn" class="btn btn-primary shadow-sm">Continue <i class="bi bi-chevron-right"></i></button>
                </div>
            </form>
            <?php if (!empty($_REQUEST['client_uuid']) && !empty($_REQUEST['redirect_uri'])) : ?>
                <div class="border-top mt-3 pt-3">
                    <p class="fs-5 mb-1">Do you already have an account on MajestiCloud?</p>
                    <form action="authorize.php" method="GET">
                        <input type="hidden" name="client_uuid" value="<?= $_REQUEST['client_uuid'] ?>">
                        <input type="hidden" name="redirect_uri" value="<?= $_REQUEST['redirect_uri'] ?>">
                        <?php if (!empty($_REQUEST["code_challenge"]) && !empty($_REQUEST["code_challenge_method"])) : ?>
                            <input type="hidden" name="code_challenge" value="<?= $_REQUEST['code_challenge'] ?>">
                            <input type="hidden" name="code_challenge_method" value="<?= $_REQUEST['code_challenge_method'] ?>">
                        <?php endif; ?>
                        <div>
                            <button type="submit" id="submitBtn" class="btn btn-secondary shadow-sm">Login <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <footer class="m-5">
        &copy; 2014-<?= date("Y") ?> Quentin Pugeat
    </footer>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.3.1-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.x-custom/color-modes-toggler.js"></script>
</body>

</html>