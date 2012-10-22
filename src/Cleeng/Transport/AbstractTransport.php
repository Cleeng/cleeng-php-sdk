<?php

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
