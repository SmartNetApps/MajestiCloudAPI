<?php
require_once(__DIR__."/../../engine/user/UserEngine.class.php");
$engine = new UserEngine(true);

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $engine->echo_response([
        "status" => false,
        "message" => "This endpoint requires GET."
    ], 405);
}

// Require the API key
if(!isset($_GET['api_key']) || !$engine->check_api_key($_GET['api_key'])) {
    $engine->echo_response([
        "status" => true,
        "message" => "Incorrect API Key."
    ], 403);
}

$user = $engine->select_email_validation_keys();

$engine->echo_response([
    "status" => true,
    "message" => "Successfully retrieved the keys.",
    "data" => $user
], 200);