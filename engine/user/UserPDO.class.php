<?php
require_once(__DIR__ . "/../GlobalPDO.class.php");

class UserPDO extends GlobalPDO
{
    function __construct($env)
    {
        parent::__construct($env);
    }

    public function select_user(string $value, string $col = "primary_email")
    {
        if (!in_array($col, ["primary_email", "uuid"])) return null;

        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE $col = :value");
        $stmt->bindValue("value", $value);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_email_validation_data($user_uuid, $email, $key)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE uuid = :uuid AND ((primary_email = :email AND primary_email_validation_key = :key) OR (recovery_email = :email AND recovery_email_validation_key = :key))");
        $stmt->bindValue("uuid", $user_uuid);
        $stmt->bindValue("email", $email);
        $stmt->bindValue("key", $key);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validate_email($email, $key)
    {
        $count = 0;

        $stmt = $this->pdo->prepare("UPDATE user SET primary_email_validation_key = NULL WHERE primary_email = :email AND primary_email_validation_key = :key");
        $stmt->bindValue("email", $email);
        $stmt->bindValue("key", $key);
        $stmt->execute();
        $count += $stmt->rowCount();

        $stmt = $this->pdo->prepare("UPDATE user SET recovery_email_validation_key = NULL WHERE recovery_email = :email AND recovery_email_validation_key = :key");
        $stmt->bindValue("email", $email);
        $stmt->bindValue("key", $key);
        $stmt->execute();
        $count += $stmt->rowCount();

        return $count > 0;
    }

    public function insert_user(string $primary_email, string $password_hash, string $display_name)
    {
        $stmt = $this->pdo->prepare("INSERT INTO user(uuid, password_hash, name, primary_email, primary_email_validation_key) VALUES(UUID(), :password_hash, :name, :primary_email, :primary_email_validation_key)");
        $stmt->bindValue("primary_email", $primary_email);
        $stmt->bindValue("primary_email_validation_key", bin2hex(random_bytes(64)));
        $stmt->bindValue("password_hash", $password_hash);
        $stmt->bindValue("name", $display_name);
        $executed = $stmt->execute();

        if (!$executed) throw new Exception($this->pdo->errorCode());
        return $this->pdo->lastInsertId();
    }

    public function update_user_field(string $user_uuid, string $field_name, ?string $new_value)
    {
        if (!$this->field_exists("user", $field_name)) throw new Exception("The '$field_name' field does not exist.");

        $stmt = $this->pdo->prepare("UPDATE user SET $field_name = :field_value WHERE uuid = :user_uuid");
        $stmt->bindValue("field_value", $new_value);
        $stmt->bindValue("user_uuid", $user_uuid);
        $executed = $stmt->execute();
        if (!$executed) throw new Exception($this->pdo->errorCode());
    }
}
