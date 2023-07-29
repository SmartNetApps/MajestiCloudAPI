<?php

require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $engine->echo_response([
        "status" => false,
        "message" => "This endpoint requires POST."
    ], 405);
}

$engine = new UserEngine(true);

if (empty($_POST["current"]) | empty($_POST["new"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Missing parameters."
    ], 400);
}

$password_change = $engine->update_password($_POST["current"], $_POST["new"]);

if(!$password_change) {
    $engine->echo_response([
        "status" => false,
        "message" => "Requirements not met."
    ], 400);
}

$engine->echo_response([
    "status" => false,
    "message" => "Password changed successfully."
], 200);