<?php

/**
 * Manage session-related data
 * 
 * @author Quentin Pugeat
 */
class SessionPDO extends GlobalPDO
{
    function __construct($env)
    {
        parent::__construct($env);
    }

    public function select_sessions($user_uuid)
    {
        $stmt = $this->pdo->prepare(
            "SELECT session.uuid, client_uuid, client.name as client_name, device_name, last_activity_on, last_activity_ip 
            FROM session 
            INNER JOIN client ON client.uuid = session.client_uuid
            WHERE session.user_uuid = :user_uuid"
        );
        $stmt->bindValue("user_uuid", $user_uuid);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a new session
     */
    public function insert_session($client_uuid, $device_name, $user_ip_addr, $token, $user_uuid)
    {
        $stmt = $this->pdo->prepare("INSERT INTO session(uuid, client_uuid, device_name, last_activity_ip, token, user_uuid) VALUES(UUID(), :client_uuid, :device_name, :user_ip_addr, :token, :user_uuid)");
        $stmt->bindValue("token", $token);
        $stmt->bindValue("client_uuid", $client_uuid);
        $stmt->bindValue("device_name", $device_name);
        $stmt->bindValue("user_ip_addr", $user_ip_addr);
        $stmt->bindValue("user_uuid", $user_uuid);

        return $stmt->execute();
    }

    /**
     * Delete a session
     */
    public function delete_session($uuid) {
        $stmt = $this->pdo->prepare("DELETE FROM session WHERE uuid = :uuid");
        $stmt->bindValue("uuid", $uuid);

        return $stmt->execute();
    }
}
