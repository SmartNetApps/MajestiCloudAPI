<?php
require_once(__DIR__ . "/../GlobalPDO.class.php");

class SquarenotePDO extends GlobalPDO
{
    function __construct($env)
    {
        parent::__construct($env);
    }
}
