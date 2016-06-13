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
 * @link http://developers.cleeng.com/v3/Reference/Vod_Offer_API
 */
class Cleeng_Entity_VodOffer extends Cleeng_Entity_Base
{
    protected $id;

    protected $publisherEmail;

    protected $url;

    protected $shortUrl;

    protected $title;

    protected $description;

    protected $price;

    protected $currency;

    protected $applicableTaxRate;

    protected $background;

    protected $createdAt;

    protected $updatedAt;

    protected $tags;
}
