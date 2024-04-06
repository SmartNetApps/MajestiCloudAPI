<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/OAuthPDO.class.php");
require_once(__DIR__ . "/../client/ClientPDO.class.php");
require_once(__DIR__ . "/../user/UserPDO.class.php");
require_once(__DIR__ . "/../session/SessionPDO.class.php");

use OTPHP\TOTP;

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
    public function select_user($value, $col = "primary_email")
    {
        return $this->user_pdo->select_user($value, $col);
    }

    /**
     * Get client data
     */
    public function select_client($client_uuid)
    {
        return $this->client_pdo->select_client($client_uuid);
    }

    /**
     * Get client permissions
     */
    public function get_client_permissions(string $client_uuid)
    {
        return $this->client_pdo->select_client_permissions($client_uuid);
    }

    /**
     * Generate and insert a new authorization code for a given client and authenticated user
     */
    public function create_authorization_code(string $user_uuid, string $client_uuid, $code_verifier = null)
    {
        $user = $this->select_user($user_uuid, "uuid");
        $require_mfa = !empty($user["totp_secret"]);

        $random_code = bin2hex(random_bytes(64));
        $insert = $this->pdo->insert_authorization($random_code, $user_uuid, $client_uuid, $code_verifier, $require_mfa);
        if (!$insert) throw new Exception("Failed to create the authorization code.");
        return $random_code;
    }

    public function get_authorization(string $code, string $client_uuid)
    {
        return $this->pdo->select_authorization($code, $client_uuid);
    }

    public function clear_authorizations($client_uuid, $user_uuid)
    {
        $this->pdo->delete_authorization($client_uuid, $user_uuid);
    }

    public function create_session($client_uuid, $user_uuid)
    {
        $random_token = bin2hex(random_bytes(64));
        $insert = $this->session_pdo->insert_session($client_uuid, $this->device_name(), $this->end_user_ip_address(), $random_token, $user_uuid);
        if (!$insert) throw new Exception("Failed to create the session token.");

        $client = $this->client_pdo->select_client($client_uuid);
        $user = $this->user_pdo->select_user_from_token($random_token);

        $client_table = '<p>' . $client["name"] . '</p>';
        $client_table .= '<p>' . $client["webpage"] . '</p>';

        if (empty($user["primary_email_validation_key"])) {
            $this->mailer->send_mail(
                $user["primary_email"],
                "New session opened on your account",
                "Hello " . $user["name"] . ", ",
                "We just wanted to let you know that a new session has been opened on MajestiCloud with the following client:<br>"
                    . $client_table .
                    '<br>If you don\'t remember opening a session with this app, please connect on MajestiCloud immediately to revoke the session, and secure your account by changing your password.'
            );
        }

        if (!empty($user["recovery_email"]) && empty($user["recovery_email_validation_key"])) {
            $this->mailer->send_mail(
                $user["recovery_email"],
                "New session opened on your account",
                "Hello " . $user["name"] . ", ",
                "We just wanted to let you know that a new session has been opened on MajestiCloud with the following client:<br>"
                    . $client_table .
                    '<br>If you don\'t remember opening a session with this app, please connect on MajestiCloud immediately to revoke the session, and secure your account by changing your password.'
            );
        }

        return $random_token;
    }

    function check_totp($authorization_code, $client_uuid, $totp_input)
    {
        // $secret = $this->current_session["user"]["totp_secret"];
        $authorization = $this->get_authorization($authorization_code, $client_uuid);
        $user = $this->user_pdo->select_user($authorization["user_uuid"], "uuid");

        $otp = TOTP::createFromSecret($user["totp_secret"]);
        $verify = $otp->verify($totp_input);

        if ($verify) {
            $this->pdo->set_authorization_mfa_requirement($authorization_code, false);
        }

        return $verify;
    }
}
