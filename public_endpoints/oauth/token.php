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
if (empty($_POST["authorization_code"]) || empty($_POST["client_uuid"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Missing parameters."
    ], 400);
}

// Check if the authorization exists
$authorization = $engine->get_authorization(trim($_POST["authorization_code"]), trim($_POST["client_uuid"]));
if($authorization === false) {
    $engine->echo_response([
        "status" => false,
        "message" => "Could not recognize the authorization code."
    ], 401);
}

if(!empty($authorization["pkce_code_verifier"])) {
    if(empty($_POST["code_verifier"])) {
        $engine->echo_response([
            "status" => false,
            "message" => "You must supply a PKCE Code Verifier as the authorization was created using this method."
        ], 400);
    }

    if($authorization["pkce_code_verifier"] != trim($_POST["code_verifier"])) {
        $engine->echo_response([
            "status" => false,
            "message" => "The supplied PKCE Code Verifier does not match the one used for the authorization."
        ], 401);
    }
}
else {
    if(empty($_POST["client_secret"])) {
        $engine->echo_response([
            "status" => false,
            "message" => "You must supply a Client Secret as the authorization was created without PKCE Code Verifier."
        ], 400);
    }

    $client = $engine->select_client(trim($_POST["client_uuid"]));
    if($client["secret_key"] != trim($_POST["client_secret"])) {
        $engine->echo_response([
            "status" => false,
            "message" => "Could not recognize the client secret key."
        ], 403);
    }
}

$token = $engine->create_session(trim($_POST["client_uuid"]), $authorization["user_uuid"]);

$engine->clear_authorizations(trim($_POST["client_uuid"]), $authorization["user_uuid"]);

$engine->echo_response([
    "status" => true,
    "message" => "Successfully authenticated.",
    "access_token" => $token
], 200);
