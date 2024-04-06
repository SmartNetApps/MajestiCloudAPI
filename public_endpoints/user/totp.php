<?php

require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");

switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        $engine = new UserEngine(true);
        $totp = $engine->enable_totp();

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully enabled TOTP.",
            "secret" => $totp["secret"],
            "provisioning_uri" => $totp["provisioning_uri"]
        ], 200);
        break;
    case "DELETE":
        $engine = new UserEngine(true);
        $totp_uri = $engine->disable_totp();

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully disabled TOTP."
        ], 200);
        break;
    default:
        $engine = new UserEngine(false);
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
