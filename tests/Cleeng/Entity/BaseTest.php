<?php

require_once __DIR__ . '/../../TestEntity.php';

class Cleeng_Entity_BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Cleeng_Exception_RuntimeException
     */
    public function testCollectionThrowsExceptionWhenItIsNotPopulated()
    {
        $entity = new Cleeng_TestEntity();
        $test = $entity->id;
    }

    public function testPopulateAllowsAccessingObjectProperties()
    {
        $entity = new Cleeng_TestEntity();
        $entity->populate(array('id' => 99, 'title' => 'Something'));

        $this->assertEquals(99, $entity->id);
        $this->assertEquals('Something', $entity->title);
    }


}
