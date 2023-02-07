<?php

require_once(__DIR__ . "/../../engine/session/SessionEngine.class.php");
$engine = new SessionEngine(true);

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        $engine->echo_response([
            "status" => true,
            "message" => "Session information available.",
            "data" => $engine->current_session()
        ], 200);
        break;
    case "DELETE":
        $engine->logout();
        
        $engine->echo_response([
            "status" => true,
            "message" => "Successfully logged out."
        ], 200);
        break;
    default:
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
