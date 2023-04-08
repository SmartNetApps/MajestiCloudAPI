<?php
require_once(__DIR__ . "/../GlobalEngine.class.php");
require_once(__DIR__ . "/SquarenoteDataProcessor.class.php");
require_once(__DIR__ . "/SquarenotePDO.class.php");

/**
 * API engine used for Square Note-related endpoints.
 * 
 * @author Quentin Pugeat
 */
class SquarenoteEngine extends GlobalEngine
{
    protected $pdo;
    protected $dataprocessor;

    function __construct($require_session)
    {
        parent::__construct($require_session, "squarenote");

        $this->dataprocessor = new SquarenoteDataProcessor($this->current_session()["user_uuid"]);
        $this->pdo = new SquarenotePDO($this->environment);
    }
}
