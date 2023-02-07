<?php
require_once(__DIR__ . "/../engine/GlobalEngine.class.php");
$engine = new GlobalEngine(false);

$engine->echo_response([
    "status" => true,
    "message" => "Welcome :)",
    "logged_in" => $engine->check_session(),
    "session" => $engine->current_session()
], 200);
