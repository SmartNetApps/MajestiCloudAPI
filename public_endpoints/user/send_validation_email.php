<?php
require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");
require_once(__DIR__ . "/../../engine/mailer/Mailer.class.php");
$engine = new UserEngine(true);

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $engine->echo_response([
        "status" => false,
        "message" => "This endpoint requires GET."
    ], 405);
}

if (empty($_GET["for"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Missing parameters."
    ], 400);
}

$mailer = new Mailer();
$keys = $engine->select_email_validation_keys();

switch ($_GET["for"]) {
    case "primary":
        if (empty($keys["primary_email_validation_key"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "This email address is not pending for validation."
            ], 409);
        }

        $mailer->validation_email($engine->current_session()["user"]["primary_email"], $keys["primary_email_validation_key"]);
        break;
    case "secondary":
    case "recovery":
        if (empty($keys["recovery_email_validation_key"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "This email address is not pending for validation."
            ], 409);
        }

        $mailer->validation_email($engine->current_session()["user"]["recovery_email"], $keys["recovery_email_validation_key"]);
        break;
    default:
        $engine->echo_response([
            "status" => false,
            "message" => "Unacceptable request. You must specify which email you want the user to validate with the following values: 'primary' or 'recovery'."
        ], 400);
        break;
}

$engine->echo_response([
    "status" => true,
    "message" => "Successfully sent the validation email."
], 200);
