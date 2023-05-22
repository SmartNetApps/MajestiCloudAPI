<?php

require_once(__DIR__ . "/../engine/client/ClientEngine.class.php");

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        if (!empty($_GET["uuid"])) {
            
            $engine = new ClientEngine(false);
            $client = $engine->get_client(trim($_GET["uuid"]));

            if (empty($client)) {
                $engine->echo_response([
                    "status" => false,
                    "message" => "Client not found."
                ], 404);
            }

            unset($client["secret_key"]);
            $engine->echo_response([
                "status" => true,
                "message" => "Client information available.",
                "data" => $client
            ], 200);
        }

        $engine = new ClientEngine(true);
        $engine->echo_response([
            "status" => true,
            "message" => "The clients you administrate are listed here.",
            "data" => $engine->get_my_clients()
        ], 200);
        break;
    case "POST":
        $engine = new ClientEngine(true);
        if (empty($_POST["name"]) || empty($_POST["author_name"]) || empty($_POST["logo_url"]) || empty($_POST["webpage"]) || empty($_POST["description"]) || empty($_POST["callback_url"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing input parameters."
            ], 400);
        }

        $creation = $engine->create_client(
            trim($_POST["name"]),
            trim($_POST["logo_url"]),
            trim($_POST["author_name"]),
            trim($_POST["webpage"]),
            trim($_POST["description"]),
            trim($_POST["callback_url"])
        );

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully created the client.",
            "data" => $creation
        ], 201);
        break;
    case "PATCH":
        $engine = new ClientEngine(true);
        if (empty($_PATCH)) {
            $_PATCH = [];
            $engine->parse_raw_http_request($_PATCH);
        }

        if (empty($_PATCH)) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing request body."
            ], 400);
        }

        $client_uuid = $_PATCH["uuid"];
        unset($_PATCH["uuid"]);
        $engine->update_client($client_uuid, $_PATCH);

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully updated client data."
        ], 200);
        break;
    case "DELETE":
        $engine = new ClientEngine(true);
        if (empty($_GET["uuid"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing input parameters."
            ], 400);
        }

        $deletion = $client->delete_client($_GET["uuid"]);

        $engine->echo_response([
            "status" => $deletion,
            "message" => $deletion ? "Successfully deleted the client." : "Internal failure."
        ], $deletion ? 200 : 500);
    default:
        $engine = new ClientEngine(false);
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
