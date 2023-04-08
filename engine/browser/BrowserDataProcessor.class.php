<?php
require_once(__DIR__ . "/../GlobalDataProcessor.class.php");

class BrowserDataProcessor extends GlobalDataProcessor
{
    function __construct(string $user_uuid)
    {
        parent::__construct("browser", $user_uuid);
    }

    function read_commits(array $uuids) {
        $commits = [];
        foreach ($uuids as $uuid) {
            $commit = $this->read_json($uuid);
            if(!empty($commit)) $commits[] = $this->read_json($uuid);
        }
        return $commits;
    }

    function save_commit(string $uuid, string $raw_content)
    {
        return $this->write_file($uuid, $raw_content);
    }
}
