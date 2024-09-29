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

        $users = $engine->get_client_administrators($_GET["client_uuid"]);
        $user_content_dir = __DIR__ . "/../../user_content/";

        for ($i = 0; $i < count($users); $i++) {
            $full_path = $user_content_dir . $users[$i]["profile_picture_path"];
            if (!is_file($full_path) || stripos(mime_content_type($full_path), "image/") === false) continue;

            $users[$i]["profile_picture_path"] = "data:" . mime_content_type($full_path) . ";base64," . base64_encode(file_get_contents($full_path));
        }

        $engine->echo_response([
            "status" => true,
            "message" => "Request successful.",
            "data" => $users
        ], 200);
        break;
    case "POST":
        if (empty($_POST["client_uuid"]) || empty($_POST["user_email"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing parameters."
            ], 400);
        }

        $engine->add_user_to_client_administrators($_POST["client_uuid"], $_POST["user_email"]);

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully added user to administrators."
        ], 200);
        break;
    case "DELETE":
        if (empty($_GET["client_uuid"]) || empty($_GET["user_uuid"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing parameters."
            ], 400);
        }
        
        $engine->remove_user_from_client_administrators($_GET["client_uuid"], $_GET["user_uuid"]);

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully revoked access."
        ], 200);
        break;
    default:
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
