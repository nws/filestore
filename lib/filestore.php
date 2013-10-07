<?php

require_once dirname(realpath(__FILE__)).'/fileclient.php';
require_once dirname(realpath(__FILE__)).'/volume_map.php';

function file_store_get_file_path($fid, $variant = null) {
	$volume_id = file_client_get_volume_id($fid);
	if (!isset($variant)) {
		$variant = 'original';
	}

	$dir = rtrim($GLOBALS['_filestore_config']['store'], '/').'/'.$volume_id.'/';
	if (!is_dir($dir)) {
		mkdir($dir);
	}

	$dir = $dir.$variant.'/';
	if (!is_dir($dir)) {
		mkdir($dir);
	}

	return $dir.$fid;
}

function file_store_send_file($path) {
	$fh = fopen($path, 'r');
	fpassthru($fh);
}

function file_store_accept_file($fid, $variant, $replication) {
	$in_fh = fopen('php://input', 'r');
	$fh = fopen('php://memory', 'w+');
	stream_copy_to_stream($in_fh,  $fh);

	$path = file_store_get_file_path($fid, $variant);
	if (file_exists($path)) {
		if (!$replication) {
			error_log('ERROR: file exists');
		}
		else {
			error_log("will not replicate to self\n");
		}
		return;
	}

	fseek($fh, 0, SEEK_SET);
	$rv = file_put_contents($path, $fh);

	if ($replication) {
		// do not replicated further if we got this as part of a replication
		return;
	}

	$volume_id = file_client_get_volume_id($fid);

	$volume_uris = volume_map_get_volume_uris($volume_id);

	foreach ($volume_uris as $vuri) {
		$furi = file_client_make_uri($vuri, $fid, array(
			'replication' => 'replication',
			'variant' => $variant,
		));
		fseek($fh, 0, SEEK_SET);
		file_client_upload_file($furi, array('fh' => $fh));
	}
}

