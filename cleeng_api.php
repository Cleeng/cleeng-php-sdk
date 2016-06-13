<?php
/**
 * Cleeng PHP SDK (http://cleeng.com)
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * @link    https://github.com/Cleeng/cleeng-php-sdk for the canonical source repository
 * @package Cleeng_PHP_SDK
 */

/*
 * Load all Cleeng classes
 *
 * This file can be used to include whole Cleeng API for environments without
 * autoloader (like WordPress plugin)
 */
foreach (array(
            'Api.php',
            'Entity/Base.php',
            'Entity/Customer.php',
            'Entity/SingleOffer.php',
            'Entity/EventOffer.php',
            'Entity/VodOffer.php',
            'Entity/RemoteAuth.php',
            'Entity/CustomerToken.php',
            'Entity/Associate.php',
            'Entity/Collection.php',
            'Entity/CouponCampaign.php',
            'Entity/SubscriptionCouponCampaign.php',
            'Entity/ListCouponCampaign.php',
            'Entity/ListSubscriptionCouponCampaign.php',
            'Entity/CustomerRental.php',
            'Entity/CustomerEmail.php',
            'Entity/CustomerLibrary.php',
            'Entity/RentalOffer.php',
            'Entity/Publisher.php',
            'Entity/CustomerSubscription.php',
            'Entity/OperationStatus.php',
            'Entity/PassOffer.php',
            'Entity/SubscriptionOffer.php',
            'Entity/AccessStatus.php',
            'Exception/ExceptionInterface.php',
            'Exception/RuntimeException.php',
            'Exception/InvalidArgumentException.php',
            'Exception/HttpErrorException.php',
            'Exception/ApiErrorException.php',
            'Exception/InvalidJsonException.php',
            'Transport/TransportInterface.php',
            'Transport/Curl.php',
                ) as $_cleeng_class_file) {

    require_once dirname(__FILE__) . '/src/Cleeng/' . $_cleeng_class_file;

}

unset($_cleeng_class_file);
