<?php
require_once(__DIR__ .'/config.php');

// Calling PHP Bootstrap
require_once(__DIR__ . '/php-bootstrap/bootstrap.php');

$project_env = PHPBootstrap\bootstrap(__FILE__);

// Including subproject which will set it's own environment in $sub_roject_env
require_once(__DIR__ . '/subproject/setup.php');
