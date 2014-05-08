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
 * Helper class for performing HTTP requests to Cleeng API endpoint
 */
class Cleeng_Transport_Curl implements Cleeng_Transport_TransportInterface
{

    /**
     * CURL handle
     *
     * @var resource
     */
    private $curlHandle;

    /**
     * URL used in last request
     *
     * @var string
     */
    private $lastUrl;

    /**
     * Default options for curl
     * @var array
     */
    private $defaultCurlOptions = array(
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CONNECTTIMEOUT => 10,
    );

    /**
     * Current options for curl
     * @var array
     */
    private $curlOptions = array();

    /**
     * Create curl handle and inject options
     *
     * @param $url
     * @return resource
     */
    private function getCurlHandle($url)
    {
        if (null == $this->curlHandle || $url != $this->lastUrl) {
            $this->curlHandle = curl_init($url);
            curl_setopt_array($this->curlHandle, $this->curlOptions);
        }

        return $this->curlHandle;
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        // initialize curl options variable
        $this->curlOptions = $this->defaultCurlOptions;
    }

    /**
     * Override/add options to curl handle
     *
     * @param array $options
     * @return Cleeng_Transport_Curl
     */
    public function setCurlOptions(array $options)
    {
        $this->curlOptions = $this->defaultCurlOptions;
        foreach ($options as $key => $value) {
            $this->curlOptions[$key] = $value;
        }
        $this->curlHandle = null;
        return $this;
    }

    /**
     * Send data to API endpoint using CURL
     *
     * @param $url
     * @param $data
     * @throws Cleeng_Exception_HttpErrorException
     * @return string
     */
    public function call($url, $data)
    {
        $ch = $this->getCurlHandle($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $buffer = curl_exec($ch);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            throw new Cleeng_Exception_HttpErrorException("cURL error ($errno): " . curl_error($ch));
        }

        $apiResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($apiResponseCode !== 200) {
            throw new Cleeng_Exception_HttpErrorException('Invalid HTTP response code (' . $apiResponseCode . ').');
        }

        if (!strlen($buffer)) {
            throw new Cleeng_Exception_HttpErrorException('No data received.');
        }

        return $buffer;
    }
}
