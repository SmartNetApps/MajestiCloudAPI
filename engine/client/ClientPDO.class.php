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

    public function insert_client(string $name, string $logo_url, string $author_name, string $webpage, string $description, string $callback_url, string $secret_key)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO client(uuid, name, logo_url, author_name, webpage, description, callback_url, secret_key)
            VALUES(UUID(), :name, :logo_url, :author_name, :webpage, :description, :callback_url, :secret_key)"
        );
        $stmt->bindValue("name", $name);
        $stmt->bindValue("logo_url,", $logo_url,);
        $stmt->bindValue("author_name", $author_name);
        $stmt->bindValue("webpage", $webpage);
        $stmt->bindValue("description", $description);
        $stmt->bindValue("callback_url", $callback_url);
        $stmt->bindValue("secret_key", $secret_key);

        $executed = $stmt->execute();

        if (!$executed) throw new Exception($this->pdo->errorCode());
        return $this->pdo->lastInsertId();
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
