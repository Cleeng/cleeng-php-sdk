<?php

require_once __DIR__ . '/../TestEntity.php';

class Cleeng_ApiTest extends PHPUnit_Framework_TestCase
{

    public function testApiPopulatesGivenEntity()
    {
        $entity = new Cleeng_TestEntity();

        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->with('https://api.cleeng.com/2.1/json-rpc', '[{"jsonrpc":"2.0","id":1,"method":"testMethod","params":[]}]')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"id":10,"title":"Foo Bar"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->api('testMethod', array(), $entity);
        $this->assertEquals(10, $entity->id);
        $this->assertEquals("Foo Bar", $entity->title);
    }

}
