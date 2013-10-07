<?php

$map = array();
foreach (range(0, 255) as $vid) {
	$map[ $vid ] = array(
		'http://localhost/fs/server.php?'.http_build_query(array(
			'fs' => rand(0, 10),
			'vid' => $vid,
		), null, '&'),
		'http://localhost/fs/server.php?'.http_build_query(array(
			'fs' => rand(0, 10),
			'vid' => $vid,
		), null, '&'),
	);
}

return $map;
