<?php

require_once(__DIR__ . "/../../engine/oauth/OAuthEngine.class.php");
$engine = new OAuthEngine();
$error = "";
$alert = "";

try {
    // Check for mendatory values
    if (empty($_REQUEST["client_uuid"]) || empty($_REQUEST["redirect_uri"])) {
        $error .= " Missing parameters in the request.";
    }

    // Check if the supplied client_uuid exists
    $client_exists = $engine->check_client($_REQUEST["client_uuid"]);
    if (!$client_exists) {
        $error .= " Invalid client UUID.";
    }

    // Check if the supplied redirect_uri is correct
    $client = $engine->select_client($_REQUEST["client_uuid"]);
    if ($client["callback_url"] !== $_REQUEST["redirect_uri"]) {
        $error .= " Invalid redirect URI.";
    }

    // If the form is POSTed
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if all mendatory parameters are supplied
        if (empty($_POST["username"]) || empty($_POST["password"])) {
            $alert = "Invalid credentials.";
        } else {
            // Check if the username+password combination is valid
            $valid = $engine->check_credentials(trim($_POST["username"]), trim($_POST["password"]));
            if (!$valid) {
                $alert = "Invalid credentials.";
            } else {
                // Get the PKCE Code Verifier, if there is one
                $code_verifier = null;
                if (!empty($_POST["code_challenge"])) {
                    switch ($_POST["code_challenge_method"]) {
                        case "S256":
                            $error .= " We don't support SHA256 hashed code challenges yet. Please provide a code_challenge in plain text or base64 encoded.";
                            break;
                        case "base64":
                            $code_verifier = base64_decode($_POST["code_challenge"]);
                            break;
                        case "plain":
                        default:
                            $code_verifier = $_POST["code_challenge"];
                            break;
                    }

                    if ((strlen($code_verifier) < 43 || strlen($code_verifier) > 128)) {
                        $error .= " The PKCE code verifier must be between 43 and 128 characters long.";
                    }
                }

                // Create an authorization code for the authenticated user and client 
                $user = $engine->select_user($_POST["username"]);
                $code = $engine->create_authorization_code($user["uuid"], $_POST["client_uuid"], $code_verifier);
                if ($code === false) {
                    $error .= " Internal failure while trying to create an authorization code.";
                } else {
                    http_response_code(307);
                    header("Location: ".$client["callback_url"] . "?code=$code");
                }
            }
        }
    }
} catch (Exception $ex) {
    // This is to prevent JSON-printing of errors
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log into MajestiCloud</title>
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
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($alert)) : ?>
            <p>Please wait...</p>
        <?php else : ?>
            <h2>Log into MajestiCloud</h2>
            <div class="pb-3 mb-3 border-bottom d-flex flex-row gap-4">
                <div>
                    <img width="50" src="<?= $client["logo_url"] ?>" alt="Logo de l'application">
                </div>
                <div>
                    <p class="m-0 h4"><?= $client["name"] ?></p>
                    <p class="m-0"><?= $client["author_name"] ?></p>
                    <p class="m-0"><?= $client["description"] ?></p>
                    <p class="m-0"><a href="<?= $client["webpage"] ?>"><?= $client["webpage"] ?></a></p>
                </div>
            </div>
            <?php if (!empty($alert)) : ?>
                <div class="alert alert-info">
                    <?= $alert ?>
                </div>
            <?php endif; ?>
            <form action="authorize.php" method="POST">
                <input type="hidden" name="client_uuid" value="<?= $_REQUEST['client_uuid'] ?>">
                <input type="hidden" name="redirect_uri" value="<?= $_REQUEST['redirect_uri'] ?>">
                <?php if (!empty($_REQUEST["code_challenge"]) && !empty($_REQUEST["code_challenge_method"])) : ?>
                    <input type="hidden" name="code_challenge" value="<?= $_REQUEST['code_challenge'] ?>">
                    <input type="hidden" name="code_challenge_method" value="<?= $_REQUEST['code_challenge_method'] ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label for="emailInput" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="emailInput" placeholder="name@example.com" name="username" value="<?php if (isset($_GET["username"])) echo $_GET["username"]; ?>" autocomplete="username" required>
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
                <div class="border-top mt-3 pt-3">
                    <p class="fs-5 mb-1">No account on MajestiCloud yet?</p>
                    <a class="btn btn-secondary shadow-sm" href="newaccount.php">Create an account <i class="bi bi-chevron-right"></i></a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <script>
        function showform() {
            document.getElementsByTagName("form").item(0).style.display = "block";
            document.getElementById("session-disclaimer").classList.remove("d-flex");
            document.getElementById("session-disclaimer").style.display = "none";
        }
    </script>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.3.1-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://assets.lesmajesticiels.org/libraries/bootstrap/bootstrap-5.x-custom/color-modes-toggler.js"></script>
</body>

</html>