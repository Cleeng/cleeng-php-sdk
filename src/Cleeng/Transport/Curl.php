<?php

class Cleeng_Transport_Curl extends Cleeng_Transport_AbstractTransport
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
     * Send data to API endpoint using CURL
     *
     * @param $url
     * @param $data
     * @throws Cleeng_Exception_HttpErrorException
     * @return string
     */
    public function call($url, $data)
    {
        if (null == $this->curlHandle || $url != $this->lastUrl) {
            $this->curlHandle = curl_init($url);
        }

        $ch = $this->curlHandle;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        /**
         * TODO: Validate certificate
         */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $buffer = curl_exec($ch);

        $err = curl_errno($ch);
        if ($err != 0) {
            throw new Cleeng_Exception_HttpErrorException("cURL error ($err): " . curl_error($ch));
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
