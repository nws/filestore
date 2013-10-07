<?php

require_once dirname(realpath(__FILE__)).'/fileclient.php';
require_once dirname(realpath(__FILE__)).'/volume_map.php';

function file_store_get_file_path($fid) {
	$volume_id = file_client_get_volume_id($fid);
	return rtrim($GLOBALS['_filestore_config']['store'], '/').'/'.$volume_id.'/'.$fid;
}

function file_store_send_file($path) {
	$fh = fopen($path, 'r');
	fpassthru($fh);
}

function file_store_accept_file($fid, $replication) {
	$in_fh = fopen('php://input', 'r');
	$fh = fopen('php://memory', 'w+');
	stream_copy_to_stream($in_fh,  $fh);

	$path = file_store_get_file_path($fid);
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
		));
		fseek($fh, 0, SEEK_SET);
		file_client_upload_file($furi, array('fh' => $fh));
	}
}

