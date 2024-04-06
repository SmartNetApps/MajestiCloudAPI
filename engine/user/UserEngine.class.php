<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/UserPDO.class.php");
require_once(__DIR__ . "/../mailer/Mailer.class.php");

use OTPHP\TOTP;

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

    function does_user_exist($email)
    {
        $user = $this->pdo->select_user($email);
        return $user !== false && !empty($user);
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

            // Specific treatment of email updates
            $new_key = bin2hex(random_bytes(64));
            $mailer = new Mailer();

            if ($key == "primary_email") {
                $this->pdo->update_user_field($user_uuid, "primary_email_validation_key", $new_key);
                $mailer->validation_email($value, $new_key);
            } elseif ($key == "recovery_email") {
                $this->pdo->update_user_field($user_uuid, "recovery_email_validation_key",  !empty($value) ? $new_key : null);
                if (!empty($value)) $mailer->validation_email($value, $new_key);
            }
        }
    }

    function update_password($current, $new_raw)
    {
        $user = $this->pdo->select_user($this->current_session()["user"]["primary_email"]);
        if (!password_verify($current, $user["password_hash"])) return false;

        $this->pdo->update_user_field($this->current_session()["user"]["uuid"], "password_hash", password_hash($new_raw, PASSWORD_BCRYPT));

        return true;
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

    function select_email_validation_keys()
    {
        $user = $this->pdo->select_user($this->current_session()["user"]["primary_email"]);
        return [
            "primary_email_validation_key" => $user["primary_email_validation_key"],
            "recovery_email_validation_key" => $user["recovery_email_validation_key"]
        ];
    }

    function validate_email($email, $key)
    {
        return $this->pdo->validate_email($email, $key);
    }

    function enable_totp()
    {
        $user_uuid = $this->current_session()["user"]["uuid"];

        // Generate secret
        $otp = TOTP::generate();
        $otp->setLabel('MajestiCloud');
        $totp_secret = $otp->getSecret();

        // Save it
        $this->pdo->update_user_field($user_uuid, "totp_secret", $totp_secret);

        // Return new settings
        return [
            "secret" => $totp_secret,
            "provisioning_uri" => $otp->getProvisioningUri()
        ];
    }

    function disable_totp()
    {
        $user_uuid = $this->current_session()["user"]["uuid"];
        return $this->pdo->update_user_field($user_uuid, "totp_secret", null);
    }
}
