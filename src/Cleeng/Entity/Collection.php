<?php

class Cleeng_Entity_Collection extends Cleeng_Entity_Base implements IteratorAggregate
{

    protected $entityType;

    protected $items = array();

    public function __construct($entityType = 'Cleeng_Entity_Base')
    {
        parent::__construct();
        $this->entityType = $entityType;
    }

    public function populate($data)
    {
        $this->items = array();
        foreach ($data as $item) {
            $object = new $this->entityType();
            $object->populate($item);
            $this->items[] = $object;
        }
        $this->pending = false;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        if ($this->pending) {
            throw new Cleeng_Exception_RuntimeException("Requested object is not received yet: call batchCommit() first.");
        }
        return new ArrayIterator($this->items);
    }
}
