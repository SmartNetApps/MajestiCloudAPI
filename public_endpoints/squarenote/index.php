<?php

require_once(__DIR__."/../../engine/squarenote/SquarenoteEngine.class.php");
$engine = new SquarenoteEngine(true);

$engine->echo_response([
    "status" => true,
    "message" => "The Square Note Sync services are running."
], 200);