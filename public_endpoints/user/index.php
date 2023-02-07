<?php

require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        $engine = new UserEngine(true);

        $engine->echo_response([
            "status" => true,
            "message" => "User information available.",
            "data" => $engine->current_session()["user"]
        ], 200);
        break;
    case "POST":
        $engine = new UserEngine(false);

        if (empty($_POST["email"]) | empty($_POST["password"]) | empty($_POST["name"]) | empty($_POST["api_key"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Missing parameters."
            ], 400);
        }

        if (!$engine->check_api_key($_POST["api_key"])) {
            $engine->echo_response([
                "status" => false,
                "message" => "Wrong API Key."
            ], 403);
        }

        $uuid = $engine->create_user($_POST["email"], $_POST["password"], htmlspecialchars($_POST["name"]));

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully created the user."
        ], 201);
        break;
    case "PATCH":
        $engine = new UserEngine(true);

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

        $engine->update_user($_PATCH);

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully updated user data."
        ], 200);
        break;
    case "DELETE":
        $engine = new UserEngine(true);
        if(!empty($_GET["reverse"]) && $_GET["reverse"] == "true") {
            $engine->reverse_user_deletion();
            $engine->echo_response([
                "status" => true,
                "message" => "Successfully canceled the deletion request for this user account."
            ], 200);
        }

        $to_be_deleted_on = $engine->current_session()["user"]["to_be_deleted_after"];
        if (!empty($to_be_deleted_on)) {
            $engine->echo_response([
                "status" => true,
                "message" => "This account will be deleted after ".substr($to_be_deleted_on, 0, 10)."."
            ], 200);
        }

        $to_be_deleted_on = $engine->schedule_user_deletion();

        $engine->echo_response([
            "status" => true,
            "message" => "This account will be deleted after $to_be_deleted_on."
        ], 200);
        break;
    default:
        $engine = new UserEngine(false);
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
