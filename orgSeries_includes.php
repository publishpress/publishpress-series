<?php
$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root.'/wp-load.php')) {
	require_once($root.'/wp-load.php');
} 

require_once(ABSPATH . 'wp-admin/admin.php');
?>