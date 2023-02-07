<?php

/**
 * MajestiCloud's global PDO, enables database querying
 */
class GlobalPDO
{
    protected PDO $pdo;

    function __construct(Environment $env)
    {
        $this->pdo = new PDO("mysql:host=".$env->item("DB_HOST").";dbname=".$env->item("DB_SCHEMA"), $env->item("DB_USER"), $env->item("DB_PWD"));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function field_exists(string $table_name, string $field_name)
    {
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$table_name` LIKE :field_name;");
        $stmt->bindValue("field_name", $field_name);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function select_session_from_token($token)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM session WHERE token = :token");
        $stmt->bindValue("token", $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_user_from_token($token)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE uuid = (SELECT user_uuid FROM session WHERE token = :token)");
        $stmt->bindValue("token", $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_client_from_token($token)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM client WHERE uuid = (SELECT client_uuid FROM session WHERE token = :token)");
        $stmt->bindValue("token", $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_permissions_of_session($token)
    {
        $stmt = $this->pdo->prepare("SELECT permission.*, client_has_permission.can_read, client_has_permission.can_write FROM client_has_permission INNER JOIN permission ON permission.id = client_has_permission.permission_id WHERE client_uuid = (SELECT client_uuid FROM session WHERE token = :token)");
        $stmt->bindValue("token", $token);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
