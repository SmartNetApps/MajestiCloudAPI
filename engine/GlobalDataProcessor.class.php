<?php

/**
 * This class will allow engines to get and set data in the /user_content/client_data folders
 */
class GlobalDataProcessor
{
    private string $base_path;

    function __construct(string $scope, string $user_uuid)
    {
        $this->base_path = __DIR__ . "/../user_content/client_data/$scope/$user_uuid";

        if (!is_dir($this->base_path)) {
            mkdir($this->base_path);
        }
    }

    private function path_to(string $file_uuid)
    {
        return $this->base_path . "/" . $file_uuid;
    }

    public function read_file(string $file_uuid)
    {
        return file_get_contents($this->path_to($file_uuid));
    }

    public function read_json(string $file_uuid)
    {
        return json_decode($this->read_file($file_uuid));
    }

    public function write_file(string $file_uuid, mixed $content)
    {
        return file_put_contents($this->path_to($file_uuid), $content);
    }

    public function write_json(string $file_uuid, string $content)
    {
        return $this->write_file($file_uuid, json_encode($content));
    }
}
