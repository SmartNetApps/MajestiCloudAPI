<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__."/OAuthPDO.class.php");
require_once(__DIR__."/../client/ClientPDO.class.php");
require_once(__DIR__."/../user/UserPDO.class.php");
require_once(__DIR__."/../session/SessionPDO.class.php");

class OAuthEngine extends GlobalEngine
{
    protected $pdo;
    private $client_pdo;
    private $user_pdo;
    private $session_pdo;

    function __construct()
    {
        parent::__construct(false);

        $this->pdo = new OAuthPDO($this->environment);
        $this->client_pdo = new ClientPDO($this->environment);
        $this->user_pdo = new UserPDO($this->environment);
        $this->session_pdo = new SessionPDO($this->environment);
    }

    /**
     * Check if a client exists
     */
    public function check_client($client_uuid)
    {
        $client = $this->client_pdo->select_client($client_uuid);
        return $client !== false;
    }

    /**
     * Check the validity of a username + password combination
     */
    public function check_credentials($username, $clear_password)
    {
        $user = $this->user_pdo->select_user($username);
        if ($user === false) return false;

        return password_verify($clear_password, $user["password_hash"]);
    }

    /**
     * Get user data
     */
    public function select_user($username) {
        return $this->user_pdo->select_user($username);
    }

    /**
     * Get client data
     */
    public function select_client($client_uuid) {
        return $this->client_pdo->select_client($client_uuid);
    }

    /**
     * Generate and insert a new authorization code for a given client and authenticated user
     */
    public function create_authorization_code(string $user_uuid, string $client_uuid, $code_verifier = null)
    {
        $random_code = bin2hex(random_bytes(64));
        $insert = $this->pdo->insert_authorization($random_code, $user_uuid, $client_uuid, $code_verifier);
        if(!$insert) throw New Exception("Failed to create the authorization code.");
        return $random_code;
    }

    public function get_authorization(string $code, string $client_uuid)
    {
        return $this->pdo->select_authorization($code, $client_uuid);
    }

    public function clear_authorizations($client_uuid, $user_uuid) {
        $this->pdo->delete_authorization($client_uuid, $user_uuid);
    }

    public function create_session($client_uuid, $user_uuid) {
        $random_token = bin2hex(random_bytes(64));
        $insert = $this->session_pdo->insert_session($client_uuid, $this->device_name(), $this->end_user_ip_address(), $random_token, $user_uuid);
        if(!$insert) throw New Exception("Failed to create the session token.");
        return $random_token;
    }
}
