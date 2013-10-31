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
 * @link http://cleeng.com/open/v3/Reference/Single_Offer_API
 */
class Cleeng_Entity_SingleOffer extends Cleeng_Entity_Base
{
    protected $id;

    protected $publisherEmail;

    protected $url;

    protected $title;

    protected $description;

    protected $price;

    protected $applicableTaxRate;

    protected $currency;

    protected $socialCommissionRate;

    protected $contentType;

    protected $contentExternalId;

    protected $contentExternalData;

    protected $contentAgeRestriction;

    protected $tags;

    protected $active;

    protected $createdAt;

    protected $updatedAt;
}
