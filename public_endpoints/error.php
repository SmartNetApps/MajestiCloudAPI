<?php
header("Content-Type: application/json");
echo json_encode(["status" => false, "message" => "Endpoint not found or unavailable."]);
