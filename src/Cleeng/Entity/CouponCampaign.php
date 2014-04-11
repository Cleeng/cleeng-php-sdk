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
 * Provides information about coupon campaign.
 *
 * @link http://cleeng.com/open/v3/Reference/Customer_API
 */
class Cleeng_Entity_CouponCampaign  extends Cleeng_Entity_Base
{

    protected $id;

    protected $name;

    protected $coupons;

    protected $discount;

    protected $usagePerUser;

    protected $applicableOnOfferIds;

    protected $maxUsages;

    protected $expirationDate;

    protected $associateEmail;

    protected $active;

} 