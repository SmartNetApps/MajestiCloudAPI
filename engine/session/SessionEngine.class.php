<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/SessionPDO.class.php");

/**
 * API engine used for Session-related endpoints.
 * 
 * @author Quentin Pugeat
 */
class SessionEngine extends GlobalEngine
{
    protected $pdo;

    function __construct($require_session)
    {
        parent::__construct($require_session, "session");

        $this->pdo = new SessionPDO($this->environment);
    }

    /**
     * Fetches and returns all opened sessions of the currently logged user.
     */
    public function my_sessions()
    {
        $sessions = $this->pdo->select_sessions($this->current_session()["user"]["uuid"]);
        return $sessions;
    }

    /**
     * Permanently closes the current session.
     * 
     * @return bool
     */
    public function logout() {
        return $this->pdo->delete_session($this->current_session()["uuid"]);
    }

    /**
     * Permanently closes a session.
     * 
     * @param string $uuid Session UUID to close
     * @return bool
     */
    public function close_session($uuid) {
        return $this->pdo->delete_session($uuid);
    }
}
