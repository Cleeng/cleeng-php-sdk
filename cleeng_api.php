<?php
/**
 * Load all Cleeng classes 
 */
foreach (array(
	    'AbstractTransport.php',
	    'Transport/Curl.php',
	    'TransferObject.php',
	    'Api.php'
		) as $file) {

    require_once dirname(__FILE__) . '/src/Cleeng/' . $file;
}