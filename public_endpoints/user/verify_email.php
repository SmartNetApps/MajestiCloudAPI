<?php
require_once(__DIR__."/../../engine/user/UserEngine.class.php");
$engine = new UserEngine(true);

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $engine->echo_response([
        "status" => false,
        "message" => "This endpoint requires GET."
    ], 405);
}

if(empty($_GET["email"]) || empty($_GET["key"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Missing parameters."
    ], 400);
}

$user = $engine->current_session()["user"];

if(!in_array($_GET["email"], [$user["primary_email"], $user["recovery_email"]])) {
    $engine->echo_response([
        "status" => false,
        "message" => "This email address is not associated with the logged profile."
    ], 400);
}

if(!$engine->check_email_validation_key($_GET["email"], $_GET["key"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Wrong or expired validation key."
    ], 400);
}

if($_GET["email"] == $user["primary_email"]) $engine->validate_email("primary");
elseif($_GET["email"] == $user["recovery_email"]) $engine->validate_email("recovery");

$engine->echo_response([
    "status" => true,
    "message" => "Successfully verified the email address."
], 200);