<?php

require_once__DIR__ . '/vendor/autoload.php';
$inputString = file_get_contents('php://input');
error_log($inputString);

?>