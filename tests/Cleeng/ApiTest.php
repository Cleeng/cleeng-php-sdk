<?php
/**
 * Cleeng PHP SDK (http://cleeng.com)
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * @link    https://github.com/Cleeng/cleeng-php-sdk for the canonical source repository
 * @package Cleeng_PHP_SDK
 */

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

    public function testCreateSingleOffer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"result":{"id":"A285380620_FR","publisherEmail":"publisher@something.com","url":"http:\/\/something.com","title":"Lorem Ipsum Dolor","description":"","currency":"EUR","socialCommissionEnabled":false,"socialCommissionRate":"0.00","contentType":"article","contentExternalId":0,"contentExternalData":null,"averageRating":4,"contentAgeRestriction":0,"active":true,"createdAt":1353340324,"updatedAt":1353340324,"price":0.99,"tags":[]},"id":"1","error":null,"jsonrpc":"2.0"}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXXXX');
        $entity = $api->createSingleOffer(array(
            'title' => 'Lorem Ipsum Dolor',
            'url' => 'http://something.com',
            'price' => 0.99
        ));

        $this->assertInstanceOf('Cleeng_Entity_SingleOffer', $entity);
        $this->assertEquals('publisher@something.com', $entity->publisherEmail);
        $this->assertEquals('Lorem Ipsum Dolor', $entity->title);
        $this->assertEquals('0.99', $entity->price);
    }

    public function testUpdateSingleOffer()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"result":{"id":"A285380620_FR","publisherEmail":"publisher@something.com","url":"http:\/\/something.com","title":"New title","description":"","currency":"EUR","socialCommissionEnabled":false,"socialCommissionRate":"0.00","contentType":"article","contentExternalId":0,"contentExternalData":null,"averageRating":4,"contentAgeRestriction":0,"active":true,"createdAt":1353340324,"updatedAt":1353340324,"price":0.99,"tags":[]},"id":"1","error":null,"jsonrpc":"2.0"}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setPublisherToken('XXXXX');
        $entity = $api->updateSingleOffer('A285380620_FR', array(
            'title' => 'New title',
            'url' => 'http://something.com',
            'price' => 0.99
        ));

        $this->assertInstanceOf('Cleeng_Entity_SingleOffer', $entity);
        $this->assertEquals('publisher@something.com', $entity->publisherEmail);
        $this->assertEquals('New title', $entity->title);
        $this->assertEquals('0.99', $entity->price);
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

    public function testGetAssociate()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"result":{"id":466275823,"email":"associate@domain.com","name":"John Doe","currency":"EUR","locale":"en_US","country":"US","firstName":"","lastName":"","siteName":null,"publisherData":null,"licenseType":"plug_and_go"},"id":"1","error":null,"jsonrpc":"2.0"}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setDistributorToken('XXXXX');
        $entity = $api->getAssociate('associate@domain.com');
        $this->assertInstanceOf('Cleeng_Entity_Associate', $entity);
        $this->assertEquals('associate@domain.com', $entity->email);
        $this->assertEquals('en_US', $entity->locale);
        $this->assertEquals('US', $entity->country);
    }

    public function testCreateAssociate()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"result":{"id":466275823,"email":"associate@domain.com","name":"John Doe","currency":"EUR","locale":"en_US","country":"US","firstName":"","lastName":"","siteName":null,"publisherData":null,"licenseType":"plug_and_go"},"id":"1","error":null,"jsonrpc":"2.0"}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setDistributorToken('XXXXX');
        $entity = $api->createAssociate(array(
            'email' => 'associate@domain.com',
            'locale' => 'en_US',
            'country' => 'US',
        ));
        $this->assertInstanceOf('Cleeng_Entity_Associate', $entity);
        $this->assertEquals('associate@domain.com', $entity->email);
        $this->assertEquals('en_US', $entity->locale);
        $this->assertEquals('US', $entity->country);
    }

    public function testUpdateAssociate()
    {
        $transport = $this->getMock('Cleeng_Transport_AbstractTransport', array('call'));
        $transport->expects($this->once())->method('call')
            ->will($this->returnValue('[{"result":{"id":466275823,"email":"new_associate_email@domain.com","name":"John Doe","currency":"EUR","locale":"en_US","country":"US","firstName":"","lastName":"","siteName":null,"publisherData":null,"licenseType":"plug_and_go"},"id":"1","error":null,"jsonrpc":"2.0"}]'));

        $api = new Cleeng_Api();
        $api->setTransport($transport);
        $api->setDistributorToken('XXXXX');
        $entity = $api->updateAssociate(
            'old_associate_email@domain.com',
            array(
            'email' => 'new_associate_email@domain.com',
        ));
        $this->assertInstanceOf('Cleeng_Entity_Associate', $entity);
        $this->assertEquals('new_associate_email@domain.com', $entity->email);
        $this->assertEquals('en_US', $entity->locale);
        $this->assertEquals('US', $entity->country);
    }

}
