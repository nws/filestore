<?php

function volume_map_get_volume_uris($volume_id) {
	static $volume_map = null;
	if ($volume_map === null) {
		$volume_map = require_once $GLOBALS['_filestore_config']['volumes'];
	}

	return $volume_map[ $volume_id ];
}
