<?php

require_once(__DIR__ . "/../../engine/oauth/OAuthEngine.class.php");
$engine = new OAuthEngine();

// Check the method
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $engine->echo_response([
        "status" => false,
        "message" => "This endpoint requires POST."
    ], 405);
}

// Check if all mendatory parameters are supplied
if (empty($_POST["username"]) || empty($_POST["password"]) || empty($_POST["client_uuid"]) || empty($_POST["redirect_uri"]) || empty($_POST["api_key"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Missing parameters."
    ], 400);
}

if (!$engine->check_api_key($_POST["api_key"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "The supplied API Key is wrong."
    ], 403);
}

// Check if the supplied client_uuid exists
$client_exists = $engine->check_client($_POST["client_uuid"]);
if (!$client_exists) {
    $engine->echo_response([
        "status" => false,
        "message" => "This client UUID does not exist."
    ], 400);
}

$client = $engine->select_client($_POST["client_uuid"]);
if ($client["callback_url"] !== $_POST["redirect_uri"]) {
    $engine->echo_response([
        "status" => false,
        "message" => "Wrong redirect URI."
    ], 400);
}

// Check if the username+password combination is valid
$valid = $engine->check_credentials(trim($_POST["username"]), trim($_POST["password"]));
if (!$valid) {
    $engine->echo_response([
        "status" => false,
        "message" => "Wrong username of password."
    ], 401);
}

// Get the PKCE Code Verifier, if there is one
$code_verifier = null;
if (!empty($_POST["code_challenge"])) {
    switch ($_POST["code_challenge_method"]) {
        case "S256":
            $engine->echo_response([
                "status" => false,
                "message" => "We don't support SHA256 hashed code challenges yet. Please provide a code_challenge in plain text or base64 encoded."
            ], 500);
            break;
        case "base64":
            $code_verifier = base64_decode($_POST["code_challenge"]);
            break;
        case "plain":
        default:
            $code_verifier = $_POST["code_challenge"];
            break;
    }

    if ((strlen($code_verifier) < 43 || strlen($code_verifier) > 128)) {
        $engine->echo_response([
            "status" => false,
            "message" => "The PKCE code verifier must be between 43 and 128 characters long."
        ], 400);
    }
}

// Create an authorization code for the authenticated user and client 
$user = $engine->select_user($_POST["username"]);
$code = $engine->create_authorization_code($user["uuid"], $_POST["client_uuid"], $code_verifier);
if ($code === false) {
    $engine->echo_response([
        "status" => false,
        "message" => "Internal failure."
    ], 500);
}

$engine->echo_response([
    "status" => true,
    "message" => "Successfully authorized.",
    "code" => $code,
    "redirect_to" => $client["callback_url"] . "?code=$code"
], 200);
