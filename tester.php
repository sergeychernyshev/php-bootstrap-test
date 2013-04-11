<?php

require_once(__DIR__ . '/config.php');

$use_colors_in_cli = array_key_exists('c', getopt("c"));

function colorize($string, $outcome) {
	global $use_colors_in_cli;

	if (!$use_colors_in_cli) {
		return $string;
	}

	if ($outcome) {
		$color = '0;32';
	} else {
		$color = '0;31';
	}

	return "\033[" . $color . 'm' . $string . "\033[0m";
}

$request_methods = array(
	'direct' => array(
		'title' => 'Direct',
		'request' => '/?a=b&c=d'
	),
	'path-info' => array(
		'title' => 'PATH_INFO',
		'request' => '/index.php/a/b/c/d?e=f&g=h'
	),
	'mod-rewrite' => array(
		'title' => 'mod_rewrite',
		'request' => '/a/b/c/d.html?e=f&g=h'
	)
);

$request_paths = array(
	'project' => array(
		'title' => 'Main project',
		'path' => ''
	),
	'folder' => array(
		'title' => 'Folder in the project',
		'description' => 'Sub-project called from another folder',
		'path' => '/folder'
	),
	'subproject' => array(
		'title' => 'Sub-project',
		'path' => '/subproject'
	)
);

$install_roots = array(
	'root' => array(
		'title' => 'Root of the site',
		'path' => ''
	),
	'subfolder' => array(
		'title' => 'Sub-folder',
		'path' => '/subfolder'
	),
	'alias' => array(
		'title' => 'Apache Alias',
		'path' => '/alias'
	),
	'symlink' => array(
		'title' => 'Symlink',
		'path' => '/symlink'
	),
	'port' => array(
		'title' => 'Custom port',
		'path' => '/port',
		'port' => $custom_port
	),
	'ssl' => array(
		'title' => 'SSL',
		'path' => '/ssl',
		'ssl' => true,
		'disabled' => true
	),
	'cli' => array(
		'title' => 'Command line',
		'path' => '/cli',
		'disabled' => true
	),
	'mod-vhost-alias' => array(
		'title' => 'mod_vhost_alias',
		'path' => '/vhost_alias',
		'disabled' => true
	)
);


$expected_values = array(
	'root' => array(
		'ROOT_FILESYSTEM_PATH' => $document_root,
		'ROOT_ABSOLUTE_URL_PATH' => '',
		'ROOT_FULL_URL' => 'http://' . $host
	),
	'subfolder' => array(
		'ROOT_FILESYSTEM_PATH' => $document_root . '/subfolder',
		'ROOT_ABSOLUTE_URL_PATH' => '/subfolder',
		'ROOT_FULL_URL' => 'http://' . $host . '/subfolder'
	),
	'alias' => array(
		'ROOT_FILESYSTEM_PATH' => $outside_of_document_root,
		'ROOT_ABSOLUTE_URL_PATH' => '/alias',
		'ROOT_FULL_URL' => 'http://' . $host . '/alias'
	),
	'symlink' => array(
		'ROOT_FILESYSTEM_PATH' => $outside_of_document_root,
		'ROOT_ABSOLUTE_URL_PATH' => '/symlink',
		'ROOT_FULL_URL' => 'http://' . $host . '/symlink'
	),
	'port' => array(
		'ROOT_FILESYSTEM_PATH' => $document_root,
		'ROOT_ABSOLUTE_URL_PATH' => '/port',
		'ROOT_FULL_URL' => 'http://' . $host . ':' . $custom_port . '/port'
	)
);

function getResults($host, $install_roots, $request_paths, $request_methods) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	$results = array();
	foreach ($install_roots as $install_root_id => $install_root) {
		if (isset($install_root['disabled'])) {
			continue;
		}

		foreach ($request_paths as $request_path_id => $request_path) {
			foreach ($request_methods as $request_method_id => $request_method) {
				$url = 'http' . (isset($install_root['ssl']) && $install_root['ssl'] ? 's' : '') . '://' .
						$host .
						(isset($install_root['port']) ? ':' . $install_root['port'] : '') .
						$install_root['path'] .
						$request_path['path'] .
						$request_method['request'] .
						'&json=true';


				curl_setopt($ch, CURLOPT_URL, $url);
				$json = curl_exec($ch);


				$result = null;
				if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
					#error_log("JSON: $json");

					$result = json_decode($json, true);
				} else {
					error_log("[$install_root_id][$request_path_id][$request_method_id] Testing URL: $url (Error: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ")");
				}

				$results[$install_root_id][$request_path_id][$request_method_id] = $result;
			}
		}
	}

	curl_close($ch);

	return $results;
}

$test_results = getResults($host, $install_roots, $request_paths, $request_methods);

$test_number = 0;
$tests_passed = 0;
$tests_failed = 0;
foreach ($expected_values as $install_root_id => $expected) {
	echo "\n== Testing Install: " . $install_roots[$install_root_id]['title'] . "==\n";

	foreach ($test_results[$install_root_id] as $request_path_id => $requests) {
		echo "\nComponents: " . $request_paths[$request_path_id]['title'] . "\n";

		foreach ($requests as $request_method_id => $test_result) {
			echo "\nRequest method: " . $request_methods[$request_method_id]['title'] . "\n";

			if (!is_array($test_result)) {
				echo "[$install_root_id][$request_path_id][$request_method_id] did not return an array\n";
				continue;
			}

#			var_export($test_result);
			foreach ($test_result as $test_result_slug => $test_result_values) {
				foreach ($expected as $key => $expected_value) {
					$test_number++;

					if ($test_result_slug == 'subproject') {
						$expected_value .= '/subproject';
					}

					echo "Test #" . sprintf('%03d', $test_number) . ". \$$test_result_slug [$key]: ";

					if ($test_result_values[$key] != $expected_value) {
						$tests_failed++;
						echo colorize('FAIL', false) . "\n$expected_value != " . colorize("$test_result_values[$key]", false) . "\n";
					} else {
						$tests_passed++;
						echo colorize('PASS', true) . " ('" . colorize($expected_value, true) . "')\n";
#						echo colorize('PASS', true) . "\n";
					}
				}
			}
		}
	}
}

echo "\n\n== Statistics ==\n";
echo "Total tests: $test_number\n";
echo "Tests passed: " . ($tests_passed > 0 ? colorize($tests_passed, true) : 0). "\n";
echo "Total tests: " . ($tests_failed > 0 ? colorize($tests_failed, false) : 0). "\n";

#var_export($test_results);