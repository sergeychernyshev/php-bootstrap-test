<?php
// Not using bootstrap for this to avoid it's own errors causing problems in testing
// You can hard-code it to your hostname if you feel SERVER_NAME doesn't work on your system
$test_server = $_SERVER['SERVER_NAME'];

require_once(dirname(__FILE__).'/php-bootstrap/bootstrap.php');

if (array_key_exists('json', $_GET)) {
	echo json_encode($_PROJECT);
	exit;
}

define(QUERY_STRING, 0);
define(PATH_INFO, 1);
define(MOD_REWRITE, 2);

function menu_link($slug, $path, $mode = QUERY_STRING, $uri = null) {
	global $_PROJECT, $test_server;

	if (is_null($uri)) {
		$uri = "http://$test_server/$path";

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

	$current_path = ltrim($_PROJECT['ROOT_ABSOLUTE_URL_PATH'], '/');

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
</style>
</head>
<body>
<h1>Test harness</h1>

Different application setup configurations:
<ol>
<li>
	<?php menu_link('root',		'') ?>
	<?php menu_link('path info',	'', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'', MOD_REWRITE) ?>
	- root of the site
</li>
<li>
	<?php menu_link('subfolder',	'subfolder') ?>
	<?php menu_link('path info',	'subfolder', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'subfolder', MOD_REWRITE) ?>
	- regular subfolder
</li>
<li>
	<?php menu_link('alias',	'alias') ?>
	<?php menu_link('path info',	'alias', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'alias', MOD_REWRITE) ?>
	- folder set up using Alias to folder outside of DocumentRoot
</li>
<li>
	<?php menu_link('symlink',	'symlink') ?>
	<?php menu_link('path info',	'symlink', PATH_INFO) ?>
	<?php menu_link('mod_rewrite',	'symlink', MOD_REWRITE) ?>
	- folder set up using a file system symlink to folder outside of DocumentRoot
</li>
<li>
	<?php menu_link('port',		'port', QUERY_STRING,
		"http://$test_server:81/port/") ?>
	<?php menu_link('path info',	'port', PATH_INFO,
		"http://$test_server:81/port/index.php/a/b/c/d/?path_info=true") ?>
	<?php menu_link('mod_rewrite',	'port', MOD_REWRITE,
		"http://$test_server:81/port/a/b/c/d.html?mod_rewrite=true") ?>
	- project on a non-default port
	<ul>
	</ul>
</li>
<li><i>TODO</i> <?php // menu_link('ssl',		'ssl') ?> - support for SSL-hosted version</li>
<li><i>TODO</i> <?php // menu_link('cli',		'cli') ?> - a script calling a command line tool using system call</li>
</ol>

<?php
define('VALID_ENTRY_POINT', TRUE);
#define("SHOW_PHP_INFO", TRUE);

require_once(dirname(__FILE__).'/php-bootstrap/debug.php');
?>

</body>
</html>
