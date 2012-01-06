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
    protected $data = array();


    public function __construct($transport)
    {
        $this->_transport = $transport;
    }

    public function hasErrors()
    {
        if ($this->_pending) {
            $this->_transport->commit();
        }
        return (bool)$this->_error;
    }

    public function getError()
    {
        if ($this->_pending) {
            $this->_transport->commit();
        }
        return $this->_error;
    }

    public function __get($name)
    {
        if ($this->_pending) {
            $this->_transport->commit();
        }
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($this->_pending) {
            $this->_transport->commit();
        }
        $this->data[$name] = $value;
        return null;
    }

    public function setData($data)
    {
        foreach  ($data as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    public function toArray()
    {
        if ($this->_pending) {
            $this->_transport->commit();
        }
        return $this->data;
    }

}