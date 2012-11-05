<?php

class Cleeng_Entity_Base
{

    /**
     * Set to "true" if batch request is still pending
     *
     * @var bool
     */
    protected $pending;

    /**
     * Popuplate object with properties
     *
     * @param $data
     * @throws Cleeng_Exception_InvalidArgumentException
     */
    public function populate($data)
    {
        if(!is_array($data) && !$data instanceof Traversable) {
            throw new Cleeng_Exception_InvalidArgumentException("Data must be an array or object implementing Traversable.");
        }
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
        $this->pending = false;
    }

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        $this->pending = true;
    }

    /**
     * Returns object property, checking if batch request is not pending
     *
     * @param $property
     * @return mixed
     * @throws Cleeng_Exception_InvalidArgumentException
     * @throws Cleeng_Exception_RuntimeException
     */
    public function __get($property)
    {
        if ($this->pending) {
            throw new Cleeng_Exception_RuntimeException("Requested object is not received yet: call batchCommit() first.");
        }
        if (!property_exists($this, $property)) {
            throw new Cleeng_Exception_InvalidArgumentException("Property '$property' does not exist'.");
        }
        return $this->$property;
    }

    /**
     * Property setter
     *
     * @param $property
     * @param $value
     * @throws Cleeng_Exception_InvalidArgumentException
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

}
