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

    public function testIsset()
    {
        $entity = new Cleeng_TestEntity();
        $entity->populate(array('id' => 99, 'title' => 'Something'));
        $this->assertTrue(isset($entity->id));
        $this->assertTrue(isset($entity->title));
        $this->assertFalse(isset($entity->nonExistingProperty));
    }


}
