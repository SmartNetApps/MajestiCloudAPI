<?php

require_once(__DIR__."/../../engine/browser/BrowserEngine.class.php");
$engine = new BrowserEngine(true);

$engine->echo_response([
    "status" => true,
    "message" => "The Browser Sync services are running."
], 200);