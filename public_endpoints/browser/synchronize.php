<?php

/**
 * The client must send all the modifications it has done since their last synchronization.
 * The server will then respond with everything it received since the client's last synchronization.
 */

require_once(__DIR__."/../../engine/browser/BrowserEngine.class.php");
$engine = new BrowserEngine(true);

if($_SERVER["REQUEST_METHOD"] != "POST") {
    $engine->echo_response([
        "status" => false,
        "message" => "This endpoint requires POST."
    ], 405);
}

if(empty($_POST["last_sync_datetime"]) || empty($_POST["new_commit"])) {
    $engine->echo_response([
        "status" => false,
        "message" => "Missing input data."
    ], 400);
}

$last_sync_datetime = DateTime::createFromFormat("Y-m-d H:i:s", $_POST["last_sync_datetime"]);
$new_commit = $_POST["new_commit"];
$decoded_new_commit = json_decode($_POST["new_commit"], true);

if($last_sync_datetime === false || $decoded_new_commit === false) {
    $engine->echo_response([
        "status" => false,
        "message" => "Could not read the input data."
    ], 400);
}

$commits_to_return = $engine->get_commits($last_sync_datetime);

$engine->save_new_commit($new_commit);

$engine->echo_response([
    "status" => true,
    "message" => "Successfully saved commit.",
    "commits" => $commits_to_return
], 200);