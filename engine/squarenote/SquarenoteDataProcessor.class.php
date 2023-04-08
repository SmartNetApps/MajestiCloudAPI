<?php
require_once(__DIR__ . "/../GlobalDataProcessor.class.php");

class SquarenoteDataProcessor extends GlobalDataProcessor
{
    function __construct(string $user_uuid)
    {
        parent::__construct("squarenote", $user_uuid);
    }
}
