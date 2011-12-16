<?php

abstract class Cleeng_AbstractTransport
{
    /**
     * Platform URL: set it to sandbox.cleeng.com for testing
     * with fake payments.
     *
     * @var string
     */
    protected $platformUrl = 'cleeng.com';

    /**
     * @abstract
     * @param string $endpoint
     * @param string $method
     * @param array $arguments
     * @return void
     */
    abstract public function call($endpoint, $method, $arguments);

}