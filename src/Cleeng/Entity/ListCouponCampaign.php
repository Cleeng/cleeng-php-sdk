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
 * Provides information about list coupon campaign.
 *
 */
class Cleeng_Entity_ListCouponCampaign extends Cleeng_Entity_Base
{
    protected $id;

    protected $name;

    protected $numberOfCoupons;

    protected $discount;

    protected $usagePerUser;

    protected $applicableOnOfferIds;

    protected $maxUsages;

    protected $expirationDate;

    protected $associateEmail;

    protected $active;
} 