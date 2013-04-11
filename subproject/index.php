<?php
// Calling PHP Bootstrap under subproject this time
require_once(__DIR__ . '/setup.php');

if (array_key_exists('json', $_GET) || PHP_SAPI === 'cli') {
	echo '{"subproject": ' . json_encode($sub_project_env) . '}';
	exit;
}

if (array_key_exists('jsonp', $_GET) || PHP_SAPI === 'cli') {
	echo 'callback({"subproject": ' . json_encode($sub_project_env) . '})';
	exit;
}
