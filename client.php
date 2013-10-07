<?php

require_once "lib/client.php";
file_client(array(
	'volumes' => 'volume_map_config.php',
));

if ($argc <= 2) {
	die("{$argv[0]} get <file_id> [variant] | put <path to file> [variant] [to fid]\n");
}

$op = $argv[1];

switch ($op) {
case 'put':
	$file_path = $argv[2];
	$variant = isset($argv[3]) ? $argv[3] : null;
	$to_fid = isset($argv[4]) ? $argv[4] : null;

	$fid = file_client_put_file(array('path' => $file_path), $variant, $to_fid);

	var_dump(array('file_id' => $fid));
	break;

case 'get':
	$fid = $argv[2];
	$variant = isset($argv[3]) ? $argv[3] : null;

	$fh = file_client_get_file_stream($fid, $variant);
	fpassthru($fh);
	break;

default:

}

