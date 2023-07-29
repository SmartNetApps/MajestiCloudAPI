<?php
require_once(__DIR__ . "/Environment.class.php");
require_once(__DIR__ . "/GlobalPDO.class.php");
require_once(__DIR__ . "/user/UserPDO.class.php");

/**
 * MajestiCloud's global engine, enables the API to function
 * 
 * @version 3
 * @author Quentin Pugeat <contact@quentinpugeat.fr>
 */
class GlobalEngine
{
    /** Environment variables */
    protected Environment $environment;

    /** PDO object, for database querying */
    private $pdo;

    /** Current session data, if applicable */
    private array $current_session;

    /**
     * Global Engine constructor.
     * Fetches the environment variables, checks the validity of the Bearer token if given by the client, 
     * checks permissions of the client on the accessed scope, updates logged user's activity history.
     * 
     * @param bool $require_session TRUE to require a session token from the requester, FALSE otherwise
     * @param string $scope Name of the accessed scope, if applicable
     *
     * @throws Exception when a session is required but not available, or when a permission is denied.
     */
    function __construct(bool $require_session, string $scope = null)
    {
        // Init the environment variables
        $this->environment = new Environment();

        set_exception_handler(array($this, 'handle_exception'));
        set_error_handler(array($this, 'handle_error'));

        // PDO initialization
        $this->pdo = new GlobalPDO($this->environment);

        if ($require_session && !$this->check_session()) {
            $this->echo_response(["status" => false, "message" => "You must be logged in to continue."], 403);
        }

        if ($this->check_session()) {
            $bearer_token = $this->bearer_token();
            $this->current_session = $this->pdo->select_session_from_token($bearer_token);
            $this->current_session["user"] = $this->pdo->select_user_from_token($bearer_token);
            $this->current_session["client"] = $this->pdo->select_client_from_token($bearer_token);
            $this->current_session["permissions"] = $this->pdo->select_permissions_of_session($bearer_token);

            // Remove secrets from the current_session variable
            unset($this->current_session["token"]);
            unset($this->current_session["user"]["password_hash"]);
            unset($this->current_session["user"]["primary_email_validation_key"]);
            unset($this->current_session["user"]["recovery_email_validation_key"]);
            unset($this->current_session["client"]["secret_key"]);

            // Check if the client is allowed to read/write on the requested endpoint
            if (isset($scope)) {
                if (!$this->check_permission($scope, ($_SERVER["REQUEST_METHOD"] == "GET" ? "read" : "write"))) {
                    $this->echo_response(["status" => false, "message" => "This client is not allowed to do this."], 403);
                }
            }

            // Update user activity date
            $user_pdo = new UserPDO($this->environment);
            $user_pdo->update_user_field($this->current_session["user"]["uuid"], "last_activity_on", date("Y-m-d H:i:s"));
        } else {
            $this->current_session = [];
        }
    }

    /**
     * Check if a given API Key is correct.
     */
    public function check_api_key($api_key)
    {
        return $api_key == $this->environment->item("API_KEY");
    }

    /**
     * Returns the current session data if exists, or null otherwise.
     */
    public function current_session()
    {
        if (!$this->check_session()) return null;
        return $this->current_session;
    }

    /**
     * Get the request headers
     * @link https://stackoverflow.com/questions/541430/how-do-i-read-any-request-header-in-php
     */
    public function request_headers()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = strtolower(substr($key, 5));
            $headers[$header] = $value;
        }
        return $headers;
    }

    /**
     * Fetches the Bearer token from the HTTP Authorization header and returns it.
     * 
     * @throws Exception when no brearer token is available 
     * @return string
     */
    public function bearer_token()
    {
        $headers = $this->request_headers();

        if (empty($headers["authorization"])) throw new Exception("An Authorization header must be supplied.");
        $authorization = $headers["authorization"];
        if (substr($authorization, 0, 6) != "Bearer") throw new Exception("The supplied Authorization header does not contain a correctly formatted Bearer token.");

        return substr($authorization, 7);
    }

    /**
     * Check the session validity based on the Bearer token placed in the request headers.
     * 
     * @return bool TRUE is the session is valid, FALSE otherwise.
     */
    public function check_session()
    {
        $headers = $this->request_headers();

        if (empty($headers["authorization"])) return false;
        $authorization = $headers["authorization"];
        if (substr($authorization, 0, 6) != "Bearer") return false;

        $token = substr($authorization, 7);

        $session = $this->pdo->select_session_from_token($token);

        if ($session === false) return false;

        return true;
    }

    /**
     * Checks if the logged client has a read or write permission in a certain scope.
     * 
     * @param string $scope Scope name
     * @param string $action "read" or "write"
     * @return bool
     */
    public function check_permission(string $scope, string $action)
    {
        if (!in_array($scope, array_column($this->current_session["permissions"], "scope"))) return false;
        foreach ($this->current_session["permissions"] as $key => $permission) {
            if ($permission["scope"] == $scope && $permission["can_" . $action] == 1) return true;
        }
        return false;
    }

    /**
     * Parse raw multipart/form-data data
     * @link https://stackoverflow.com/questions/5483851/manually-parse-raw-multipart-form-data-data-with-php/5488449#5488449
     * @author Christof
     * 
     * @param array $a_data HTTP request array.
     */
    public function parse_raw_http_request(array &$a_data)
    {
        if (empty($_SERVER["CONTENT_TYPE"])) return;
        if (stripos($_SERVER["CONTENT_TYPE"], "multipart/form-data") === false) {
            parse_str(file_get_contents('php://input'), $a_data);
            return;
        };

        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE) {
                // match "name", then everything after "stream" (optional) except for prepending newlines 
                preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
            }
            // parse all other fields
            else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }
    }

    /**
     * Fetches and returns the client IP address from the HTTP headers
     * 
     * @return string
     */
    public function end_user_ip_address()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Fetches and returns the browser and platform name from the client user agent
     */
    public function device_name()
    {
        $browser = @get_browser($_SERVER['HTTP_USER_AGENT'], true);

        if ($browser === false) return "Unknown device";
        else return $browser["browser"] . " for " . $browser["platform"];
    }

    /**
     * Outputs a JSON-formatted response to the end client and sets the specified HTTP response code.
     * 
     * @param mixed $obj Response object to output
     * @param int $http_code HTTP response code to set
     * @param bool $continue TRUE to continue running the script after the output, FALSE otherwise.
     */
    public function echo_response($obj, $http_code, $continue = false)
    {
        http_response_code($http_code);
        header("Content-Type: application/json");
        echo json_encode($obj);
        if (!$continue) exit;
    }

    /**
     * The default exception handler for the API.
     * Will send an email to the support email address set in the environment variables, and echo a failure response (HTTP 500).
     * 
     * @param Exception $ex The thrown exception
     */
    public function handle_exception($ex)
    {
        if ($this->environment->item("NOTIFY_EXCEPTIONS") == "on") {
            mail(
                $this->environment->item("SUPPORT_EMAIL"),
                "Exception thrown on " . $ex->getFile(),
                $ex->__toString(),
                "From: Exception Handler <" . $this->environment->item("EXCEPTIONHANDLER_EMAIL") . ">"
            );
        }

        if ($this->environment->item("LOG_EXCEPTIONS") == "on") {
            file_put_contents(__DIR__ . "/failure_logs/" . date("Ymd_His") . ".exception.log", $ex->__toString());
        }

        $response = [
            "status" => false,
            "message" => "Internal failure."
        ];

        if($this->environment->item("ENVIRONMENT_TYPE") == "development") {
            $response["message"] = $ex->__toString();
            $response["exception"] = [
                "message" => $ex->getMessage(),
                "code" => $ex->getCode(),
                "file" => $ex->getFile(),
                "line" => $ex->getLine(),
                "trace" => $ex->getTraceAsString()
            ];
        }

        $this->echo_response($response, 500);
    }

    public function handle_error($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}
