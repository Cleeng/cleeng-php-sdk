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

    protected $title;

    protected $currency;

    protected $country;

    protected $description;

    protected $socialCommissionEnabled;

    protected $socialCommissionRate;

    protected $contentType;

    protected $contentExternalId;

    protected $contentExternalData;

    protected $tags;

    protected $publisherId;

    protected $active;

    protected $eventStartTime;

    protected $eventEndTime;

    protected $eventRemainderEmailActivated;

    protected $eventRemainderEmailBody;

    protected $eventVideoOnDemand;

    protected $eventVideoOnDemandRentalPeriod;
}
