<?php

/**
 * This class handles the environment variables and various useful functions that enable Engines to check them.
 * The variables in .env file override the default configuration.
 */
class Environment
{
    private array $environment;

    function __construct()
    {
        // Default configuration
        $this->environment["ROOT_URL"] = "http://www.example.com/";
        $this->environment["ENVIRONMENT_TYPE"] = "development";

        $this->environment["NOTIFY_EXCEPTIONS"] = "off";
        $this->environment["LOG_EXCEPTIONS"] = "on";
        $this->environment["EXCEPTIONHANDLER_EMAIL"] = "exceptionhandler@example.com";

        $this->environment["DB_HOST"] = "example.com";
        $this->environment["DB_SCHEMA"] = "exampledb";
        $this->environment["DB_USER"] = "exampleuser";
        $this->environment["DB_PWD"] = "dummypwd";

        $this->environment["SUPPORT_EMAIL"] = "webmaster@example.com";

        $this->environment["API_KEY"] = "dummy";

        // Read the .env file, if it exists
        if (is_file(__DIR__ . "/../.env")) {
            foreach (file(__DIR__ . "/../.env") as $line) {
                if (empty(trim($line))) continue;
                if (substr($line, 0, 1) == "#") continue;
                if (stripos($line, "=") === false) continue;

                $key = explode("=", $line)[0];
                $value = explode("=", $line)[1];

                $this->environment[strtoupper(trim($key))] = trim($value);
            }
        }
    }

    /**
     * Get an environment variable
     * 
     * @param string $key Environment variable key
     * @return string Environment variable value
     */
    public function item(string $key)
    {
        if (!in_array(strtoupper(trim($key)), array_keys($this->environment))) {
            throw new EnvironmentException("$key is not set.");
        }

        return $this->environment[strtoupper(trim($key))];
    }
}

class EnvironmentException extends Exception
{
    function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
