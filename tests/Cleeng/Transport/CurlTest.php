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

class Cleeng_Transport_CurlTest extends PHPUnit_Framework_TestCase
{

    public function testTrue()
    {
        $this->assertTrue(true);
    }

    /*public function testCallCreatesTransferObject()
    {
        $transport = new Cleeng_Transport_Curl();

        $obj = $transport->call('testMethod', array('param1' => 'a', 'paramb2' => 'b'));
        $this->assertInstanceOf('Cleeng_TransferObject', $obj);
        $this->assertEquals('testMethod', $obj->_requestData['method']);
    }

    public function testCommitThrowsExceptionWhenInvalidJsonIsReceived()
    {
        $transport = $this->getMock('Cleeng_Transport_Curl', array('_curl'));
        $transport->expects($this->any())->method('_curl')->will($this->returnValue('{invalid_json[]'));
        try {
            $transport->call('testMethod', array('param1' => 'a', 'paramb2' => 'b'));
            $this->fail('Exception was not thrown.');
        } catch (Cleeng_RuntimeException $e) {
        }
    }

    public function testCommitThrowsExceptionWhenResponseHasErrors()
    {
        $transport = $this->getMock('Cleeng_Transport_Curl', array('_curl'));
        $transport->expects($this->any())
                ->method('_curl')
                ->will($this->returnValue('{"result":null,"error":{"code":-32000,"message":"Offer #xkd9p8 not found","data":{}},"id":"1","jsonrpc":"2.0"}'));
        try {
            $transport->call('testMethod', array('param1' => 'a', 'paramb2' => 'b'));
            $this->fail('Exception was not thrown.');
        } catch (Cleeng_RuntimeException $e) {
        }
    }*/
}
