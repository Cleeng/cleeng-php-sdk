<?php
/**
 * Load all Cleeng classes 
 */
foreach (array(
	    'AbstractTransport.php',
	    'Transport/Curl.php',
	    'TransferObject.php',
	    'Api.php'
		) as $_cleeng_class_file) {

    require_once dirname(__FILE__) . '/src/Cleeng/' . $_cleeng_class_file;
}

unset($_cleeng_class_file);
