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

    public function testGetSingleOffer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->with('https://api.cleeng.com/2.1/json-rpc', '[{"jsonrpc":"2.0","id":1,"method":"getSingleOffer","params":{"offerId":"A123123123_FR"}}]')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"id":"A123123123_FR","title":"Foo Bar","price":"10.99"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $entity = $api->getSingleOffer('A123123123_FR');
        $this->assertInstanceOf('Cleeng_Entity_SingleOffer', $entity);
        $this->assertEquals('Foo Bar', $entity->title);
        $this->assertEquals('10.99', $entity->price);
    }

    public function testGetRentalOffer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->with('https://api.cleeng.com/2.1/json-rpc', '[{"jsonrpc":"2.0","id":1,"method":"getRentalOffer","params":{"offerId":"R123123123_FR"}}]')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"id":"R123123123_FR","title":"Foo Bar","price":"10.99","period":"24"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $entity = $api->getRentalOffer('R123123123_FR');
        $this->assertInstanceOf('Cleeng_Entity_RentalOffer', $entity);
        $this->assertEquals('Foo Bar', $entity->title);
        $this->assertEquals('10.99', $entity->price);
        $this->assertEquals('24', $entity->period);
    }

    public function testGetSubscriptionOffer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->with('https://api.cleeng.com/2.1/json-rpc', '[{"jsonrpc":"2.0","id":1,"method":"getSubscriptionOffer","params":{"offerId":"R123123123_FR"}}]')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"id":"S123123123_FR","title":"Foo Bar","price":"10.99","period":"monthly"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $entity = $api->getSubscriptionOffer('R123123123_FR');
        $this->assertInstanceOf('Cleeng_Entity_SubscriptionOffer', $entity);
        $this->assertEquals('Foo Bar', $entity->title);
        $this->assertEquals('10.99', $entity->price);
        $this->assertEquals('monthly', $entity->period);
    }

}
