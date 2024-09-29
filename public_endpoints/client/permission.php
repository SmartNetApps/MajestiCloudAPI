<?php
require_once(__DIR__ . "/../../engine/client/ClientEngine.class.php");
$engine = new ClientEngine(true);

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        if (empty($_GET["client_uuid"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing client_uuid parameter."
            ], 400);
        }

        $permissions = $engine->get_client_permissions($_GET["client_uuid"]);

        $engine->echo_response([
            "status" => true,
            "message" => "Request successful.",
            "data" => $permissions
        ], 200);
        break;
    default:
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
