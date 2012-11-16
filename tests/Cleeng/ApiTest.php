<?php

require_once __DIR__ . '/../TestEntity.php';

class Cleeng_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cleeng_Api
     */
    private $api;

    public function setUp()
    {
        $this->api = new Cleeng_Api();
    }

    public function testApiPopulatesEntity()
    {
        $entity = new Cleeng_TestEntity();

        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"id":10,"title":"Foo Bar"}}]'));

        $this->api->setTransport($transport);
        $this->api->api('testMethod', array(), $entity);
        $this->assertEquals(10, $entity->id);
        $this->assertEquals("Foo Bar", $entity->title);
    }

    public function testGetCustomer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"displayName":"John Doe","currency":"USD","locale":"fr_FR","country":"BE"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setCustomerToken('XXX');
        $entity = $api->getCustomer();

        $this->assertInstanceOf('Cleeng_Entity_Customer', $entity);
        $this->assertEquals('John Doe', $entity->displayName);
        $this->assertEquals('USD', $entity->currency);
        $this->assertEquals('fr_FR', $entity->locale);
        $this->assertEquals('BE', $entity->country);
    }

    public function testPrepareRemoteAuth()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"url":"https://cleeng.com/remote_auth_url"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXX');
        $entity = $api->prepareRemoteAuth(array('email' => 'johndoe@domain.com'), array('offerId' => 'A123123123_US'));

        $this->assertInstanceOf('Cleeng_Entity_RemoteAuth', $entity);
        $this->assertEquals('https://cleeng.com/remote_auth_url', $entity->url);
    }

    public function testGenerateCustomerToken()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"token":"YYYYYYYY"}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXX');
        $entity = $api->generateCustomerToken('johndoe@domain.com');

        $this->assertInstanceOf('Cleeng_Entity_CustomerToken', $entity);
        $this->assertEquals('YYYYYYYY', $entity->token);
    }

    public function testUpdateCustomerEmail()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"success":true}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXX');
        $entity = $api->updateCustomerEmail('johndoe@domain.com', 'johns_new_email@domain.com');

        $this->assertInstanceOf('Cleeng_Entity_OperationStatus', $entity);
        $this->assertEquals(true, $entity->success);
    }

    public function testUpdateCustomerSubscription()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"status":"cancelled","offerId":"S111222333_US","expiresAt":1352470772}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXX');
        $entity = $api->updateCustomerSubscription('johndoe@domain.com', 'S111222333_US', array('status' => 'cancelled'));

        $this->assertInstanceOf('Cleeng_Entity_CustomerSubscription', $entity);
        $this->assertEquals('S111222333_US', $entity->offerId);
        $this->assertEquals('cancelled', $entity->status);
        $this->assertEquals('1352470772', $entity->expiresAt);
    }

    public function testUpdateCustomerRental()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"offerId":"R333222111_US","expiresAt":1352473772}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXX');
        $entity = $api->updateCustomerRental('johndoe@domain.com', 'R333222111_US', array('expiresAt' => '1352473772'));

        $this->assertInstanceOf('Cleeng_Entity_CustomerRental', $entity);
        $this->assertEquals('R333222111_US', $entity->offerId);
        $this->assertEquals('1352473772', $entity->expiresAt);
    }

    public function testListCustomerSubscriptions()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"id":1,"error":false,"result":{"items":[{"status":"active","offerId":"S111111111_US","expiresAt":1352470772}, {"status":"cancelled","offerId":"S222222222_US","expiresAt":1352470772}],"totalItemCount":2}}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXX');
        $collection = $api->listCustomerSubscriptions('johndoe@domain.com', 0, 10);

        $this->assertInstanceOf('Cleeng_Entity_Collection', $collection);

        $it = $collection->getIterator();
        $first = current($it);
        $last = next($it);

        $this->assertEquals('S111111111_US', $first->offerId);
        $this->assertEquals('active', $first->status);
        $this->assertEquals('S222222222_US', $last->offerId);
        $this->assertEquals('cancelled', $last->status);
    }

    public function testGetSingleOffer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
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
