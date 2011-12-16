<?php
/**
 * Load all Cleeng classes 
 */
foreach (return array(
    'Cleeng_AbstractTransport' => dirname(__FILE__) . '/AbstractTransport.php',
    'Cleeng_Api' => dirname(__FILE__) . '/Api.php',
    'Cleeng_Transport_Curl' => dirname(__FILE__) . '/Transport/Curl.php',
    'Cleeng_Transfer_Object' => dirname(__FILE__) . '/TransferObject.php',
)

include dirname(__FILE__) . '/Cleeng/autoload_classmap.php' as $file)
{
    require_once $file;
} 


foreach (array(
	    'AbstractTransport.php',
	    'Transport/Curl.php',
	    'TransferObject.php',
	    'Api.php'
		) as $file) {

    require_once dirname(__FILE__) . '/src/Cleeng/' . $file;
}