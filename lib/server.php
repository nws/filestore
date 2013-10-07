<?php

require_once dirname(realpath(__FILE__)).'/filestore.php';
require_once dirname(realpath(__FILE__)).'/fileclient.php';

function file_serve($config) {
	$GLOBALS['_filestore_config'] = $config;

	if (!isset($_GET['fid'])) {
		return;
	}

	switch ($_SERVER['REQUEST_METHOD']) {
	case 'PUT':
		$fid = $_GET['fid'];
		$replication = isset($_GET['replication']) ? $_GET['replication'] : '';
		$variant = isset($_GET['variant']) ? $_GET['variant'] : null;

		file_store_accept_file($fid, $variant, $replication);
		break;

	case 'GET':
		$variant = isset($_GET['variant']) ? $_GET['variant'] : null;
		$file_path = file_store_get_file_path($_GET['fid'], $variant);
		file_store_send_file($file_path);
		break;

	default:

	}
}

