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

/**
 * @link http://developers.cleeng.com/v3/Reference/Customer_API
 */
class Cleeng_Entity_CustomerLibrary extends Cleeng_Entity_Base
{
    protected $transactionId;

    protected $transactionDate;

    protected $transactionPrice;

    protected $transactionCurrency;

    protected $transactionExternalData;

    protected $publisherName;

    protected $publisherSiteUrl;

    protected $offerId;

    protected $offerType;

    protected $offerTitle;

    protected $offerDescription;

    protected $offerUrl;

    protected $invoicePrice;

    protected $invoiceCurrency;

    protected $expiresAt;

    protected $cancelled;

    protected $pending;
}
