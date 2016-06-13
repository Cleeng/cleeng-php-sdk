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
 * Placeholder for multi currency offer ID (looks like normal offer id without country
 * suffix: A123123123, S432432432, etc.)
 *
 * @link http://developers.cleeng.com/v3/Reference
 */
class Cleeng_Entity_MultiCurrencyOffer extends Cleeng_Entity_Base
{

    protected $multiCurrencyOfferId;

    protected $offers;
}
