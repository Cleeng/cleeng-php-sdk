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
 * @link http://cleeng.com/open/v3/Reference/Event_Offer_API
 */
class Cleeng_Entity_EventOffer extends Cleeng_Entity_Base
{
    protected $id;

    protected $publisherEmail;

    protected $url;

    protected $shortUrl;

    protected $title;

    protected $price;

    protected $currency;

    protected $applicableTaxRate;

    protected $startTime;

    protected $endTime;

    protected $timeZone;

    protected $applyServiceFeeOnCustomer;

    protected $active;

    protected $createdAt;

    protected $updatedAt;

    protected $tags;
}
