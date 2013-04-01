<?php
// Set to match your setup to compare to detected values
$host = 'bootstrap-test.lc';
$document_root = '/Library/WebServer/Documents/bootstrap-test';
$outside_of_document_root = '/Users/sergey/php-bootstrap-outside-docroot';
$custom_port = 81;

require_once(dirname(__FILE__).'/php-bootstrap/bootstrap.php');
$project_env = PHPBootstrap\bootstrap(__FILE__);

if (array_key_exists('json', $_GET) || PHP_SAPI === 'cli') {
	echo json_encode($project_env);
	exit;
}

$expected_values = array(
	'' => array(
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

// used to compare expected and detected values
$current_path = ltrim($project_env['ROOT_ABSOLUTE_URL_PATH'], '/');

define('QUERY_STRING', 0);
define('PATH_INFO', 1);
define('MOD_REWRITE', 2);

function menu_link($slug, $path, $mode = QUERY_STRING, $uri = null) {
	global $project_env, $host, $custom_port, $current_path;

	if (is_null($uri)) {
		$uri = "http://$host";

		if ($path) {
			$uri .= '/' . $path;
		}

		switch ($mode) {
			case QUERY_STRING:
				$uri .= '?a=b&c=d';
				break;
			case PATH_INFO:
				$uri .= '/index.php/a/b/c/d/?path_info=true';
				break;
			case MOD_REWRITE:
				$uri .= '/a/b/c/d.html?mod_rewrite=true';
				break;
		}
	}

	$current_mode = QUERY_STRING;
	if (array_key_exists('path_info', $_GET)) {
		$current_mode = PATH_INFO;
	} else if (array_key_exists('mod_rewrite', $_GET)) {
		$current_mode = MOD_REWRITE;
	}

	if ($mode == $current_mode && $path == $current_path) {
		?><b class="button"><?php echo $slug ?></b><?php
	} else {
		?><a class="button" href="<?php echo $uri ?>"><?php echo $slug ?></a><?php
	}

 	?> <!-- (<a href="<?php echo $uri ?><?php echo strstr($uri, '?') !== FALSE ? '&' : '?' ?>json" target="_blank">json</a>) --><?php
	
}
?><html>
<head>
<style>
	.variations td {
		padding: 0.5em;
	}

	th {
		text-align: left;
	}

	div.results {
		float: left;
		margin-right: 2em;
	}
	.button {
		padding: 0.2em 0.5em;
		border: 1px solid gray;
		background-color: #f2f2f2;
		border-radius: 4px;
		text-decoration: none;
	}

	b.button {
		background-color: #d8d8d8;
		border: 1px solid silver;
	}

	li {
		padding: 0.5em;
	}
	tr.fail td {
		background-color: #ff9996;
	}
	tr.pass td {
		background-color: #86f4a7;
	}
</style>
</head>
<body>

<h1>PHP Bootstrap Test Harness</h1>

<p>This is a test harness for <a target="_blank" href="https://github.com/sergeychernyshev/php-bootstrap">PHP Bootstrap</a> project.</p>

<div class="results">

<table cellpadding="15" cellspacing="0" border="1" style="margin-bottom: 1em">
<?php foreach ($project_env as $key => $val) {
	$expected_value = $expected_values[$current_path][$key];
	$detected_value = $val;

	$outcome = $detected_value == $expected_value;
?>
<tr class="<?php echo $outcome ? 'pass' : 'fail' ?>">
	<td>
		<p>$project_env['<?php echo $key ?>']</p>
		<p>Expected: <?php echo htmlentities($expected_value) ?></p>
		<p>Detected: <?php echo is_null($detected_value) ? '<i>null</i>' : htmlentities($detected_value) ?></p>
	</td>
</tr>
<?php } ?>
</table>
</div>

<div class="variations">
<h2>Installation variations</h2>
<p>You can see different application setup configurations below and see the variables that get set.</p>

<table>
<tr>
<td>
	<?php menu_link('root',		'') ?>
	<?php menu_link('path info',	'', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'', MOD_REWRITE) ?>
</td>
<th>Root of the site</th>
</tr>

<tr>
<td>
<?php menu_link('subfolder',	'subfolder') ?>
<?php menu_link('path info',	'subfolder', PATH_INFO) ?>
<?php menu_link('mod_rewrite',	'subfolder', MOD_REWRITE) ?>
</td>
<th>Regular subfolder</th>
</tr>

<tr>
<td>
<?php menu_link('alias',	'alias') ?>
<?php menu_link('path info',	'alias', PATH_INFO) ?>
<?php menu_link('mod_rewrite',	'alias', MOD_REWRITE) ?>
</td>
<th>Folder set up using Apache Alias to folder outside of DocumentRoot</th>
</tr>

<tr>
<td>
<?php menu_link('symlink',	'symlink') ?>
<?php menu_link('path info',	'symlink', PATH_INFO) ?>
<?php menu_link('mod_rewrite',	'symlink', MOD_REWRITE) ?>
</td>
<th>Folder set up using a file system symlink to folder outside of DocumentRoot</th>
</tr>

<tr>
<td>
<?php menu_link('port',		'port', QUERY_STRING,	"http://$host:$custom_port/port/") ?>
<?php menu_link('path info',	'port', PATH_INFO,	"http://$host:$custom_port/port/index.php/a/b/c/d/?path_info=true") ?>
<?php menu_link('mod_rewrite',	'port', MOD_REWRITE,	"http://$host:$custom_port/port/a/b/c/d.html?mod_rewrite=true") ?>
</td>
<th>Project on a non-default port</th>
</tr>

<tr>
<td><i>TODO</i> <?php // menu_link('ssl',		'ssl') ?></td>
<th>Support for SSL-hosted version</th>
</tr>

<tr>
<td><i>TODO</i> <?php // menu_link('cli',		'cli') ?></td>
<th>Script calling a command line tool using system call</th>
</tr>

<tr>
<td><i>TODO</i> <?php // menu_link('vhostalias',	'vhostalias') ?></td>
<th>Script installed on site that uses mod_vhost_alias (tons of bugs with DOCUMENT_ROOT)</th>
</tr>

</table>
</div>

<div style="clear: both"></div>

<h2>Variables used for calculation</h2>
<table cellpadding="15" cellspacing="0" border="1">
<?php foreach (array('SCRIPT_FILENAME', 'SCRIPT_NAME', 'HTTPS', 'HTTP_HOST', 'SERVER_PORT') as $var) { ?>
<tr><td>$_SERVER['<?php echo $var ?>']</td><td><?php
if (array_key_exists($var, $_SERVER)) {
	echo is_null($_SERVER[$var]) ? '<i>null</i>' : htmlentities($_SERVER[$var]);
} else {
	echo 'n/a';
}

?></td></tr>
<?php } ?>
</table>

<?php if (defined("SHOW_PHP_INFO")) { ?>
	<h2>More server information</h2>
	<?php
	phpinfo();
}
?>
</body>
</html>
