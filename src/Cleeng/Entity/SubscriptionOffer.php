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
 * @link http://cleeng.com/open/v3/Reference/Subscription_API
 */
class Cleeng_Entity_SubscriptionOffer extends Cleeng_Entity_Base
{

    protected $id;

    protected $price;

    protected $applicableTaxRate;

    protected $title;

    protected $currency;

    protected $country;

    protected $appliedOnTags;

    protected $publisherId;

    protected $period;

    protected $active;
}
