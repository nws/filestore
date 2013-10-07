<?php

require_once "lib/client.php";
file_client(array(
	'volumes' => 'volume_map_config.php',
));

if ($argc <= 2) {
	die("{$argv[0]} get <file_id> | put <path to file>\n");
}

$op = $argv[1];

switch ($op) {
case 'put':
	$file_path = $argv[2];

	$fid = file_client_put_file(array('path' => $file_path));

	var_dump(array('file_id' => $fid));
	break;

case 'get':
	$fid = $argv[2];

	$fh = file_client_get_file_stream($fid);
	fpassthru($fh);
	break;

default:

}

