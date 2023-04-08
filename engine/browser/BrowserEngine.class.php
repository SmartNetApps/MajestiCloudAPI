<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/BrowserDataProcessor.class.php");
require_once(__DIR__ . "/BrowserPDO.class.php");

/**
 * API engine used for Browser-related endpoints.
 * 
 * @author Quentin Pugeat
 */
class BrowserEngine extends GlobalEngine
{
    protected $pdo;
    protected $dataprocessor;

    function __construct($require_session)
    {
        parent::__construct($require_session, "browser");

        $this->dataprocessor = new BrowserDataProcessor($this->current_session()["user_uuid"]);
        $this->pdo = new BrowserPDO($this->environment);
    }

    public function get_commits(DateTime $since) {
        $commits_data = $this->pdo->select_commits($this->current_session()["user_uuid"], $since);
        return $this->dataprocessor->read_commits(array_column($commits_data, "uuid"));
    }

    public function save_new_commit(string $raw_commit_data) {
        $commit_uuid = $this->pdo->insert_commit($this->current_session()["user_uuid"]);
        $this->dataprocessor->save_commit($commit_uuid, $raw_commit_data);
        return $commit_uuid;
    }
}
