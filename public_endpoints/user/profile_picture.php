<?php

require_once(__DIR__ . "/../../engine/user/UserEngine.class.php");
$engine = new UserEngine(true);
$user_content_dir = __DIR__."/../../user_content/";

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        $profile_picture_path = $engine->current_session()["user"]["profile_picture_path"];

        if (empty($profile_picture_path)) {
            $engine->echo_response([
                "status" => false,
                "message" => "Your profile has no picture."
            ], 404);
        }
        
        $full_path = $user_content_dir.$profile_picture_path;
        if(!is_file($full_path) || stripos(mime_content_type($full_path), "image/") === false) {
            $engine->echo_response([
                "status" => false,
                "message" => "Could not read your profile picture because of an internal failure."
            ], 500);
        }

        header('Content-Type: '.mime_content_type($full_path));
        readfile($full_path);

        break;
    case "POST":
    case "PUT":
        $file = fopen("php://input", 'r');
        $tempfilepath = $user_content_dir."tmp/".date("U").".tmp";
        $tempfile = fopen($tempfilepath, "w");
        $tempfilesize = 0;
        while ($data = fread($file, 1024)) {
            $tempfilesize += 1024;
            if($tempfilesize > (1024 * 1024 * 2)) {
                fclose($tempfile);
                fclose($file);
                unlink($tempfilepath);
                $engine->echo_response([
                    "status" => false,
                    "message" => "Files larger than 2 MB are not accepted."
                ], 400);
            }
            fwrite($tempfile, $data);
        }

        if(stripos(mime_content_type($tempfilepath), "image/") === false) {
            unlink($tempfilepath);
            $engine->echo_response([
                "status" => false,
                "message" => "This file format is not accepted."
            ], 400);
        }
        else {
            if(!empty($engine->current_session()["user"]["profile_picture_path"])) unlink($user_content_dir.$engine->current_session()["user"]["profile_picture_path"]);
            $new_file_path = "profile_pictures/".$engine->current_session()["user"]["uuid"].".pic";
            rename($tempfilepath, $user_content_dir.$new_file_path);
            $engine->set_profile_picture_path($new_file_path);
            $engine->echo_response([
                "status" => true,
                "message" => "Successfully set the profile picture."
            ], 200);
        }
        break;
    case "DELETE":
        $profile_picture_path = $engine->current_session()["user"]["profile_picture_path"];
        if (empty($profile_picture_path)) {
            $engine->echo_response([
                "status" => true,
                "message" => "Your profile had no picture already."
            ], 200);
        }

        $full_path = $user_content_dir.$profile_picture_path;
        if(!is_file($full_path)) {
            $engine->echo_response([
                "status" => true,
                "message" => "Your profile had no picture already."
            ], 200);
        }

        if(!unlink($full_path)) {
            $engine->echo_response([
                "status" => false,
                "message" => "Couldn't delete your profile picture because of an internal failure."
            ], 500);
        }

        $engine->set_profile_picture_path(null);

        $engine->echo_response([
            "status" => true,
            "message" => "Successfully deleted your profile picture."
        ], 200);

        break;
    default:
        $engine->echo_response([
            "status" => false,
            "message" => $_SERVER["REQUEST_METHOD"] . " is not supported by this endpoint."
        ], 405);
}
