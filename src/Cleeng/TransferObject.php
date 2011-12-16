<?php

class Cleeng_TransferObject
{
    public $_requestData;

    public $_endpoint;

    public $_error = null;

    public $_pending = true;

    protected $_transport;

    /**
     * Object properties
     * 
     * @var array
     */
    protected $_data = array();


    public function __construct($transport)
    {
        $this->_transport = $transport;
    }


    public function __get($name)
    {
        if ($this->_pending) {
            $this->_transport->processPendingRequests();
        }
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($this->_pending) {
            $this->_transport->processPendingRequests();
        }
        $this->$name = $value;
        return null;
    }

    public function setData($data)
    {
        foreach  ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function toArray()
    {
        if ($this->_pending) {
            $this->_transport->processPendingRequests();
        }
        return $this->_data;
    }

}