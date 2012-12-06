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
 * Defines relationship between the customer and an offer.
 *
 * @link http://cleeng.com/open/v3/Reference/Customer_API
 */
class Cleeng_Entity_AccessStatus extends Cleeng_Entity_Base
{

    protected $accessGranted;

    protected $grantType;

    protected $expiresAt;

    protected $socialCommissionUrl;

}
