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
 * @link http://cleeng.com/open/v3/Reference/Live_Event_API
 */
class Cleeng_Entity_EventOffer extends Cleeng_Entity_Base
{

    protected $id;

    protected $url;

    protected $price;

    protected $applicableTaxRate;

    protected $title;

    protected $currency;

    protected $country;

    protected $description;

    protected $socialCommissionRate;

    protected $contentType;

    protected $contentExternalId;

    protected $contentExternalData;

    protected $contentAgeRestriction;

    protected $tags;

    protected $publisherId;

    protected $active;

    protected $startTime;

    protected $endTime;

    protected $remainderEmailActivated;

    protected $remainderEmailBody;

    protected $videoOnDemand;

    protected $videoOnDemandRentalPeriod;

    protected $createdAt;

    protected $updatedAt;
}
