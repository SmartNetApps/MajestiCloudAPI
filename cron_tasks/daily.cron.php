<?php
require_once(__DIR__."/../engine/Environment.class.php");
$env = new Environment();
$db = new PDO("mysql:host=".$env->item("DB_HOST").";dbname=".$env->item("DB_SCHEMA"), $env->item("DB_USER"), $env->item("DB_PWD"));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Invalidate sessions that have been inactive for more than a week
$a_week_ago = date_sub(new DateTime(), new DateInterval("P1W"))->format("Y-m-d");
$stmt = $db->prepare("DELETE FROM `session` WHERE last_activity_on < :a_week_ago");
$stmt->bindValue("a_week_ago", $a_week_ago);
$stmt->execute();