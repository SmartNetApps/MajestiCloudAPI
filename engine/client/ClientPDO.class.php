<?php
require_once(__DIR__ . "/../GlobalPDO.class.php");

class ClientPDO extends GlobalPDO
{
    function __construct($env)
    {
        parent::__construct($env);
    }

    public function select_client(string $client_uuid)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM client WHERE uuid = :client_uuid");
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_client_with_secret(string $client_secret)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM client WHERE secret_key = :client_secret");
        $stmt->bindValue("client_secret", $client_secret);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_clients_of_admin(string $user_uuid)
    {
        $stmt = $this->pdo->prepare("SELECT client.* FROM client INNER JOIN client_has_admin ON client.uuid = client_has_admin.client_uuid WHERE client_has_admin.user_uuid = :user_uuid");
        $stmt->bindValue("user_uuid", $user_uuid);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function client_has_admin_check(string $user_uuid, string $client_uuid)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM client_has_admin WHERE user_uuid = :user_uuid AND client_uuid = :client_uuid");
        $stmt->bindValue("user_uuid", $user_uuid);
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)["count"] != 0 ? true : false;
    }

    public function select_administrators_of_client(string $client_uuid)
    {
        $stmt = $this->pdo->prepare("SELECT user.uuid, user.name, user.profile_picture_path, user.primary_email FROM user INNER JOIN client_has_admin ON user.uuid = client_has_admin.user_uuid WHERE client_has_admin.client_uuid = :client_uuid");
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_administrator_of_client(string $client_uuid, string $user_uuid)
    {
        $stmt = $this->pdo->prepare("INSERT INTO client_has_admin(client_uuid, user_uuid) VALUES(:client_uuid, :user_uuid);");
        $stmt->bindValue("client_uuid", trim($client_uuid));
        $stmt->bindValue("user_uuid", trim($user_uuid));
        return $stmt->execute();
    }

    public function remove_administrator_of_client(string $client_uuid, string $user_uuid)
    {
        $stmt = $this->pdo->prepare("DELETE FROM client_has_admin WHERE client_uuid = :client_uuid AND user_uuid = :user_uuid;");
        $stmt->bindValue("client_uuid", trim($client_uuid));
        $stmt->bindValue("user_uuid", trim($user_uuid));
        return $stmt->execute();
    }

    public function select_client_permissions(string $client_uuid)
    {
        $stmt = $this->pdo->prepare("SELECT `permission`.`user_friendly_description`, `can_read`, `can_write` 
        FROM `client_has_permission`
        INNER JOIN `permission` ON `permission`.`id` = `client_has_permission`.`permission_id`
        WHERE `client_uuid` = :client_uuid");
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_client_permission(string $client_uuid, string $permission_scope, bool $can_read, bool $can_write)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO client_has_permission(client_uuid, permission_id, can_read, can_write)
            VALUES(:client_uuid, (SELECT id FROM permission WHERE scope = :permission_scope), :can_read, :can_write)"
        );
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->bindValue("permission_scope", $permission_scope);
        $stmt->bindValue("can_read", $can_read ? 1 : 0);
        $stmt->bindValue("can_write", $can_write ? 1 : 0);
        $executed = $stmt->execute();

        if (!$executed) throw new Exception($this->pdo->errorCode());
    }

    public function insert_client(string $name, string $logo_url, string $author_name, string $webpage, string $description, string $callback_url, string $secret_key)
    {
        $uuid = uniqid("cl", true);

        $stmt = $this->pdo->prepare(
            "INSERT INTO client(uuid, name, logo_url, author_name, webpage, description, callback_url, secret_key)
            VALUES(:uuid, :name, :logo_url, :author_name, :webpage, :description, :callback_url, :secret_key)"
        );
        $stmt->bindValue("uuid", $uuid);
        $stmt->bindValue("name", $name);
        $stmt->bindValue("logo_url", $logo_url);
        $stmt->bindValue("author_name", $author_name);
        $stmt->bindValue("webpage", $webpage);
        $stmt->bindValue("description", $description);
        $stmt->bindValue("callback_url", $callback_url);
        $stmt->bindValue("secret_key", $secret_key);

        $executed = $stmt->execute();

        if (!$executed) throw new Exception($this->pdo->errorCode());
        return $uuid;
    }

    public function update_client_field(string $client_uuid, string $field_name, ?string $new_value)
    {
        if (!$this->field_exists("client", $field_name)) throw new Exception("The '$field_name' field does not exist.");

        $stmt = $this->pdo->prepare("UPDATE client SET $field_name = :field_value WHERE uuid = :client_uuid");
        $stmt->bindValue("field_value", $new_value);
        $stmt->bindValue("client_uuid", $client_uuid);
        $executed = $stmt->execute();
        if (!$executed) throw new Exception($this->pdo->errorCode());
    }

    public function delete_client(string $client_uuid)
    {
        $stmt = $this->pdo->prepare("DELETE FROM client WHERE uuid = :client_uuid");
        $stmt->bindValue("client_uuid", $client_uuid);
        $executed = $stmt->execute();
        if (!$executed) throw new Exception($this->pdo->errorCode());
    }
}
