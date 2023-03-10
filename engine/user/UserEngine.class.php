<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/UserPDO.class.php");

class UserEngine extends GlobalEngine
{
    protected $pdo;

    function __construct($require_session)
    {
        parent::__construct($require_session, "user");

        $this->pdo = new UserPDO($this->environment);
    }

    /**
     * Creates a new user in the database.
     */
    function create_user(string $primary_email, string $clear_password, string $display_name)
    {
        $password_hash = password_hash($clear_password, PASSWORD_BCRYPT);

        $uuid = $this->pdo->insert_user($primary_email, $password_hash, $display_name);

        return $uuid;
    }

    /**
     * @deprecated
     */
    function get_user()
    {
        return $this->current_session()["user"];
    }

    function update_user(array $new_data)
    {
        $authorized_fields = ["name", "primary_email", "recovery_email"];
        $user_uuid = $this->current_session()["user"]["uuid"];

        foreach ($new_data as $key => $value) {
            if (!in_array($key, $authorized_fields)) throw new Exception("The '$key' field is read-only.");

            $this->pdo->update_user_field($user_uuid, $key, $value);

            if ($key == "primary_email") {
                $this->pdo->update_user_field($user_uuid, "primary_email_validation_key", bin2hex(random_bytes(64)));
            }
            if ($key == "recovery_email") {
                $this->pdo->update_user_field($user_uuid, "recovery_email_validation_key",  !empty($value) ? bin2hex(random_bytes(64)) : null);
            }
        }
    }

    function schedule_user_deletion()
    {
        $user_uuid = $this->current_session()["user"]["uuid"];
        $schedule_date = new DateTime();
        $schedule_date->add(new DateInterval("P30D"));

        $this->pdo->update_user_field($user_uuid, "to_be_deleted_after", $schedule_date->format("Y-m-d"));

        return $schedule_date->format("Y-m-d");
    }

    function reverse_user_deletion()
    {
        $user_uuid = $this->current_session()["user"]["uuid"];
        $this->pdo->update_user_field($user_uuid, "to_be_deleted_after", null);
    }

    function set_profile_picture_path(string $new_path = null)
    {
        $user_uuid = $this->current_session()["user"]["uuid"];
        $this->pdo->update_user_field($user_uuid, "profile_picture_path", $new_path);
    }

    function check_email_validation_key($email, $key) {
        $user_uuid = $this->current_session()["user"]["uuid"];
        $select = $this->pdo->select_email_validation_data($user_uuid, $email, $key);

        return $select !== false;
    }

    function validate_email($which_one) {
        $user_uuid = $this->current_session()["user"]["uuid"];
        $this->pdo->update_user_field($user_uuid, $which_one."_email_validation_key", null);
    }
}
