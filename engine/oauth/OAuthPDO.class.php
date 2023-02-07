<?php
require_once(__DIR__ . "/../GlobalPDO.class.php");

class OAuthPDO extends GlobalPDO
{
    function __construct($env)
    {
        parent::__construct($env);
    }

    public function insert_authorization(
        string $authorization_code,
        string $user_uuid,
        string $client_uuid,
        string $pkce_code_verifier = null
    ) {
        $stmt = $this->pdo->prepare("INSERT INTO oauth_authorization(authorization_key, user_uuid, client_uuid, pkce_code_verifier) VALUES (:authorization_code, :user_uuid, :client_uuid, :pkce_code_verifier)");
        $stmt->bindValue("authorization_code", $authorization_code);
        $stmt->bindValue("user_uuid", $user_uuid);
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->bindValue("pkce_code_verifier", $pkce_code_verifier);
        return $stmt->execute();
    }

    public function select_authorization(string $code, string $client_uuid)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM oauth_authorization WHERE authorization_key = :code AND client_uuid = :client_uuid");
        $stmt->bindValue("code", $code);
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete_authorization($client_uuid, $user_uuid) {
        $stmt = $this->pdo->prepare("DELETE FROM oauth_authorization WHERE user_uuid = :user_uuid AND client_uuid = :client_uuid");
        $stmt->bindValue("user_uuid", $user_uuid);
        $stmt->bindValue("client_uuid", $client_uuid);
        return $stmt->execute();
    }
}