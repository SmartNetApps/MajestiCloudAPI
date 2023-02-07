<?php

require_once(__DIR__ . "/../../engine/session/SessionEngine.class.php");
$engine = new SessionEngine(true);

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        $sessions = $engine->my_sessions();
        $engine->echo_response([
            "status" => true,
            "message" => "Session informations available.",
            "data" => $sessions
        ], 200);
        break;
    case "DELETE":
        if(!empty($_GET["uuid"])) {
            $engine->close_session($_GET["uuid"]);
        
            $engine->echo_response([
                "status" => true,
                "message" => "Successfully closed the session."
            ], 200);
        }
        else {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing parameters."
            ], 400);
        }
        break;
    default:
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
