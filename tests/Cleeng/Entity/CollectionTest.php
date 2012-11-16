<?php

require_once __DIR__ . '/../../TestEntity.php';

class Cleeng_Entity_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Cleeng_Exception_RuntimeException
     */
    public function testCollectionThrowsExceptionWhenItIsNotPopulated()
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_TestEntity');
        foreach ($collection as $entity) {

        }
    }


    public function testCollectionCreatesEntities()
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_TestEntity');
        $collection->populate(array(
            'items' => array(array(
                    'id' => 1,
                    'title' => 'Foo'
                ),
                array(
                    'id' => 2,
                    'title' => 'Bar'
                ),
            ),
            'totalItemCount' => 2
        ));

        foreach ($collection as $entity) {
            $this->assertInstanceOf('Cleeng_TestEntity', $entity);
        }

        $it = $collection->getIterator();
        $first = current($it);
        $last = next($it);

        $this->assertEquals(1, $first->id);
        $this->assertEquals('Foo', $first->title);
        $this->assertEquals(2, $last->id);
        $this->assertEquals('Bar', $last->title);
    }


}
