<?php

abstract class Cleeng_Transport_Abstract
{
    /**
     * Perform API call (or add it to queue if transport supports batch calls)
     *
     * @abstract
     * @param string $method
     * @param array $arguments
     * @return void
     */
    abstract public function call($method, $arguments);

    /**
     * Perform API call (if transport supports batch calls)
     * @abstract
     */
    abstract public function commit();

}