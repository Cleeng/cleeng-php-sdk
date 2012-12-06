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

abstract class Cleeng_Transport_AbstractTransport
{
    /**
     * Send data to API endpoint using CURL and return resulting string
     *
     * @param $url
     * @param $data
     * @return string
     */
    abstract public function call($url, $data);

}
