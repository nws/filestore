<?php

require_once dirname(realpath(__FILE__)).'/fileclient.php';

function file_client($config) {
	$GLOBALS['_filestore_config'] = $config;
}
