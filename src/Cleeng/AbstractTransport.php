<?php

abstract class Cleeng_AbstractTransport
{
    /**
     * Perform API call (or add it to queue if transport supports batch calls)
     *
     * @abstract
     * @param string $endpoint
     * @param string $method
     * @param array $arguments
     * @return void
     */
    abstract public function call($endpoint, $method, $arguments);

    /**
     * Perform API call (if transport supports batch calls)
     * @abstract
     */
    abstract public function commit();

}