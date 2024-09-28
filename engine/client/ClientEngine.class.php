<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/ClientPDO.class.php");

/**
 * API engine used for Client-related endpoints.
 * 
 * @author Quentin Pugeat
 */
class ClientEngine extends GlobalEngine
{
    protected $pdo;

    function __construct($require_session)
    {
        parent::__construct($require_session, "client");

        $this->pdo = new ClientPDO($this->environment);
    }

    /**
     * Checks if a user is an admin of a client
     * @return bool
     */
    private function is_admin(string $user_uuid, string $client_uuid) {
        return $this->pdo->client_has_admin_check($user_uuid, $client_uuid);
    }

    public function get_my_clients()
    {
        return $this->pdo->select_clients_of_admin($this->current_session()["user"]["uuid"]);
    }

    public function get_client(string $client_uuid)
    {
        return $this->pdo->select_client($client_uuid);
    }

    public function create_client(string $name, string $logo_url, string $author_name, string $webpage, string $description, string $callback_url)
    {
        $secret_key = bin2hex(random_bytes(32));
        return ["uuid" => $this->pdo->insert_client($name, $logo_url, $author_name, $webpage, $description, $callback_url, $secret_key), "secret_key" => $secret_key];
    }

    function update_client(string $client_uuid, array $new_data)
    {
        if(!$this->is_admin($this->current_session()["user"]["uuid"], $client_uuid)) {
            throw New Exception("The logged user does not has administration privileges on this client.");
        }

        $authorized_fields = ["name", "logo_url", "author_name", "webpage", "description", "callback_url"];

        foreach ($new_data as $key => $value) {
            if (!in_array($key, $authorized_fields)) throw new Exception("The '$key' field is read-only.");

            $this->pdo->update_client_field($client_uuid, $key, $value);
        }
    }

    function delete_client(string $client_uuid) {
        if(!$this->is_admin($this->current_session()["user"]["uuid"], $client_uuid)) {
            throw New Exception("The logged user does not has administration privileges on this client.");
        }

        $this->pdo->delete_client($client_uuid);
        return true;
    }
}
