<?php

require_once dirname(realpath(__FILE__)).'/volume_map.php';

function file_client_gen_id() {
	return uniqid('i');
}

function file_client_get_volume_id($key) {
	$id_source = substr($key, -2);
	$id = base_convert($id_source, 16, 10);
	return $id;
}

function file_client_get_volume_uri($volume_id) {

	$uris = volume_map_get_volume_uris($volume_id);
	return $uris[ array_rand($uris) ];
}

function file_client_make_uri($volume_uri, $fid, $params = array()) {
	$vuri = parse_url($volume_uri);
	parse_str($vuri['query'], $query);
	$query['fid'] = $fid;

	return sprintf("%s://%s%s%s?%s",
		$vuri['scheme'],
		$vuri['host'],
		(!empty($vuri['port']) ? ':'.$vuri['port'] : ''),
		$vuri['path'],
		http_build_query($query + $params, null, '&'));
}

function file_client_put_file($file_spec) {
	$fid = file_client_gen_id();
	$volume_id = file_client_get_volume_id($fid);
	$volume_uri = file_client_get_volume_uri($volume_id);
	$file_uri = file_client_make_uri($volume_uri, $fid);

	if (file_client_upload_file($file_uri, $file_spec)) {
		error_log("upload succeeded: $fid");
		return $fid;
	}
}

function file_client_upload_file($file_uri, $file_spec) {
	if (isset($file_spec['path'])) {
		$fh = fopen($file_spec['path'], 'r');
	}
	else if (isset($file_spec['fh'])) {
		$fh = $file_spec['fh'];
	}
	else if (isset($file_spec['contents'])) {
		die("uploading bytes NYI");
	}
	else {
		die("file_spec is what?\n");
	}

	if (!$fh) {
		error_log('no fh!');
		return;
	}
	error_log("PUT-ing to $file_uri");

	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $file_uri,
		CURLOPT_PUT => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_UPLOAD => true,
		CURLOPT_INFILE => $fh,
	));

	$rv = curl_exec($ch);
	$errno = curl_errno($ch);

	if ($errno) {
		error_log("curl failed: $errno: ".curl_error($ch));
		return;
	}

	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($code !== 200) {
		error_log("curl failed with http status: ".$code);
		return;
	}

	return true;
}

function file_client_get_file_uri($fid) {
	$volume_id = file_client_get_volume_id($fid);
	$volume_uri = file_client_get_volume_uri($volume_id);
	$file_uri = file_client_make_uri($volume_uri, $fid);

	return $file_uri;
}

function file_client_get_file_stream($fid) {
	$file_uri = file_client_get_file_uri($fid);

	$fh = fopen('php://memory', 'w+');

	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $file_uri,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FILE => $fh,
	));

	$rv = curl_exec($ch);

	if (!$rv) {
		return;
	}

	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($code !== 200) {
		return;
	}

	fseek($fh, 0, SEEK_SET);

	return $fh;
}
