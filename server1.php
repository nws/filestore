<?php

require_once 'lib/server.php';

file_serve(array(
	'volumes' => 'volume_map_config.php',
	'store' => 'files1/',
));


