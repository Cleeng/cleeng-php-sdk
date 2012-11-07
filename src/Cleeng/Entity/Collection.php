<?php

class Cleeng_Entity_Collection extends Cleeng_Entity_Base implements IteratorAggregate
{

    protected $entityType;

    protected $items = array();

    protected $totalItemCount;

    public function __construct($entityType = 'Cleeng_Entity_Base')
    {
        parent::__construct();
        $this->entityType = $entityType;
    }

    /**
     *
     *
     * @param $data
     * @throws Cleeng_Exception_RuntimeException
     */
    public function populate($data)
    {
        if (!isset($data['items'])) {
            throw new Cleeng_Exception_RuntimeException("Cannot create collection - items are not available.");
        }
        if (!isset($data['totalItemCount'])) {
            throw new Cleeng_Exception_RuntimeException("Cannot create collection - total item count is not available.");
        }
        $this->items = array();
        foreach ($data['items'] as $item) {
            $object = new $this->entityType();
            $object->populate($item);
            $this->items[] = $object;
        }
        $this->totalItemCount = $data['totalItemCount'];
        $this->pending = false;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @throws Cleeng_Exception_RuntimeException
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        if ($this->pending) {
            throw new Cleeng_Exception_RuntimeException("Object is not received from API yet.");
        }
        return new ArrayIterator($this->items);
    }
}
