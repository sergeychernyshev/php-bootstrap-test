<?php
require_once(__DIR__ .'/setup.php');

if (array_key_exists('json', $_GET)) {
	echo '{"project": ' . json_encode($project_env) . ', "subproject": ' . json_encode($sub_project_env) . '}';
	exit;
}

if (array_key_exists('jsonp', $_GET)) {
	echo 'callback({"project": ' . json_encode($project_env) . ', "subproject": ' . json_encode($sub_project_env) . '})';
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
	.value {
		font-family: monospace;
	}

	td, th {
		padding: 0.5em;
	}

	.variations th {
		text-align: right;
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
	td.fail {
		background-color: #ff9996;
	}
	td.pass {
		background-color: #86f4a7;
	}
</style>
</head>
<body>

<h1><a target="_blank" href="https://github.com/sergeychernyshev/php-bootstrap">PHP Bootstrap</a> Test Harness</h1>

<div class="results">

<table cellpadding="15" cellspacing="0" border="1" style="margin-bottom: 1em">
<tr>
	<td>Variable</td>
	<td>Project</td>
	<td>Sub-project</td>
</tr>
<?php foreach ($project_env as $key => $val) {
	$expected_value = $expected_values[$current_path][$key];
	$detected_value = $val;

	$outcome = $detected_value == $expected_value;

	$expected_sub_value = $expected_values[$current_path][$key] . '/subproject';
	$detected_sub_value = $sub_project_env[$key];

	$sub_outcome = $detected_sub_value == $expected_sub_value;
?>
<tr>
	<td>
		<p><?php echo $key ?></p>
	</td>
	<td class="<?php echo $outcome ? 'pass' : 'fail' ?>">
		<?php if ($outcome) { ?>
			<span class="value">
				<?php echo htmlentities($detected_value) ?>
			</span>
		<?php } else { ?>
			<p>Expected:
				<span class="value">
					<?php echo htmlentities($expected_value) ?>
				</span>
			</p>
			<p>Detected:
				<span class="value">
					<?php echo is_null($detected_value) ? '<i>null</i>' : htmlentities($detected_value) ?>
				</span>
			</p>
		<?php } ?>
	</td>
	<td class="<?php echo $sub_outcome ? 'pass' : 'fail' ?>">
		<?php if ($sub_outcome) { ?>
			<span class="value">
				<?php echo htmlentities($detected_sub_value) ?>
			</span>
		<?php } else { ?>
			<p>Expected:
				<span class="value">
					<?php echo htmlentities($expected_sub_value) ?>
				</span>
			</p>
			<p>
				Detected:
				<span class="value">
					<?php echo is_null($detected_sub_value) ? '<i>null</i>' : htmlentities($detected_sub_value) ?>
				</span>
			</p>
		<?php } ?>
	</td>
</tr>
<?php } ?>
</table>
</div>

<div class="variations">
<p>You can see different application setup configurations below and see the variables that get set.</p>

<table>
<tr>
<th>Root of the site</th>
<td>
	<nobr>
	<?php menu_link('root',		'') ?>
	<?php menu_link('path info',	'', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'', MOD_REWRITE) ?>
	</nobr>
</td>
</tr>

<tr>
<th>Regular subfolder</th>
<td>
	<nobr>
	<?php menu_link('subfolder',	'subfolder') ?>
	<?php menu_link('path info',	'subfolder', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'subfolder', MOD_REWRITE) ?>
	</nobr>
</td>
</tr>

<tr>
<th>Folder set up using Apache Alias to folder outside of DocumentRoot</th>
<td>
	<nobr>
	<?php menu_link('alias',	'alias') ?>
	<?php menu_link('path info',	'alias', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'alias', MOD_REWRITE) ?>
	</nobr>
</td>
</tr>

<tr>
<th>Folder set up using a file system symlink to folder outside of DocumentRoot</th>
<td>
	<nobr>
	<?php menu_link('symlink',	'symlink') ?>
	<?php menu_link('path info',	'symlink', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'symlink', MOD_REWRITE) ?>
	</nobr>
</td>
</tr>

<tr>
<th>Project on a non-default port</th>
<td>
	<nobr>
<?php menu_link('port',		'port', QUERY_STRING,	"http://$host:$custom_port/port/") ?>
<?php menu_link('path info',	'port', PATH_INFO,	"http://$host:$custom_port/port/index.php/a/b/c/d/?path_info=true") ?>
<?php menu_link('mod_rewrite',	'port', MOD_REWRITE,	"http://$host:$custom_port/port/a/b/c/d.html?mod_rewrite=true") ?>
	</nobr>
</td>
</tr>

<tr>
<th>Support for SSL-hosted version</th>
<td><i>TODO</i> <?php // menu_link('ssl',		'ssl') ?></td>
</tr>

<tr>
<th>Script calling a command line tool using system call</th>
<td><i>TODO</i> <?php // menu_link('cli',		'cli') ?></td>
</tr>

<tr>
<th>Script installed on site that uses mod_vhost_alias (tons of bugs with DOCUMENT_ROOT)</th>
<td><i>TODO</i> <?php // menu_link('vhostalias',	'vhostalias') ?></td>
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
