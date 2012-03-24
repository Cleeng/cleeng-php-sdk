<?php
/**
 * Load all Cleeng classes
 *
 * This file can be used to include whole Cleeng API for environments without
 * autoloader (like WordPress plugin)
 */
foreach (array(
            'Api.php',            
            'InvalidArgumentException.php',
            'RuntimeException.php',
            'Transport/Abstract.php',
            'Transport/Curl.php',
            'TransferObject.php',
                ) as $_cleeng_class_file) {

    require_once dirname(__FILE__) . '/src/Cleeng/' . $_cleeng_class_file;
}

unset($_cleeng_class_file);
