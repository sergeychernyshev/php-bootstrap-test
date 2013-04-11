<?php
// Calling PHP Bootstrap under subproject this time
require_once(dirname(__DIR__) . '/setup.php');

if (array_key_exists('json', $_GET)) {
	echo '{"project": ' . json_encode($project_env) . ', "subproject": ' . json_encode($sub_project_env) . '}';
	exit;
}

if (array_key_exists('jsonp', $_GET)) {
	echo 'callback({"project": ' . json_encode($project_env) . ', "subproject": ' . json_encode($sub_project_env) . '})';
	exit;
}