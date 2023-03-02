<?php
require_once(__DIR__ . "/../GlobalPDO.class.php");

class BrowserPDO extends GlobalPDO
{
    function __construct($env)
    {
        parent::__construct($env);
    }

    public function select_commits(string $user_uuid, DateTime $from_datetime) {
        $stmt = $this->pdo->prepare("SELECT * FROM browser_commits WHERE user_uuid = :user_uuid AND saved_on > :from_datetime ORDER BY saved_on ASC;");
        $stmt->bindValue("user_uuid", $user_uuid);
        $stmt->bindValue("from_datetime", $from_datetime->format("Y-m-d H:i:s"));
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert_commit(string $user_uuid) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO browser_commits(uuid, user_uuid, saved_on) VALUES(UUID(), :user_uuid, NOW())"
        );
        $stmt->bindValue("user_uuid", $user_uuid);

        $executed = $stmt->execute();

        if (!$executed) throw new Exception($this->pdo->errorCode());
        return $this->pdo->lastInsertId("uuid");
    }
}
