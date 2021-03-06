<?php
/*
 * This file is part of the FreshDoctrineEnumBundle
 *
 * (c) Artem Genvald <genvaldartem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fresh\DoctrineEnumBundle\Tests\Validator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fresh\DoctrineEnumBundle\FreshDoctrineEnumBundle;
use Symfony\Component\DependencyInjection\Container;

/**
 * FreshDoctrineEnumBundleTest.
 *
 * @author Artem Genvald <genvaldartem@gmail.com>
 */
class FreshDoctrineEnumBundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Container|\PHPUnit_Framework_MockObject_MockObject */
    private $container;

    /** @@var Registry|\PHPUnit_Framework_MockObject_MockObject */
    private $doctrine;

    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
                                ->disableOriginalConstructor()
                                ->setMethods(['get'])
                                ->getMock();

        $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                               ->disableOriginalConstructor()
                               ->setMethods(['getConnections'])
                               ->getMock();

        $this->container->expects($this->once())
                        ->method('get')
                        ->with('doctrine')
                        ->willReturn($this->doctrine);

    }

    protected function tearDown()
    {
        unset($this->container);
        unset($this->doctrine);
    }

    public function testEnumMappingRegistration()
    {
        /**
         * @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject $databasePlatformAbc
         * @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject $databasePlatformDef
         */
        $databasePlatformAbc = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');
        $databasePlatformDef = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');

        $connectionAbc = $this->getMockBuilder('Doctrine\DBAL\Connection')
                              ->disableOriginalConstructor()
                              ->setMethods(['getDatabasePlatform'])
                              ->getMock();

        $connectionAbc->expects($this->once())
                      ->method('getDatabasePlatform')
                      ->willReturn($databasePlatformAbc);

        $connectionDef = $this->getMockBuilder('Doctrine\DBAL\Connection')
                              ->disableOriginalConstructor()
                              ->setMethods(['getDatabasePlatform'])
                              ->getMock();

        $connectionDef->expects($this->once())
                      ->method('getDatabasePlatform')
                      ->willReturn($databasePlatformDef);

        $this->doctrine->method('getConnections')
                       ->willReturn([$connectionAbc, $connectionDef]);

        $bundle = new FreshDoctrineEnumBundle();
        $bundle->setContainer($this->container);
        $bundle->boot();

        $this->assertTrue($databasePlatformAbc->hasDoctrineTypeMappingFor('enum'));
        $this->assertEquals('string', $databasePlatformAbc->getDoctrineTypeMapping('enum'));

        $this->assertTrue($databasePlatformDef->hasDoctrineTypeMappingFor('enum'));
        $this->assertEquals('string', $databasePlatformDef->getDoctrineTypeMapping('enum'));
    }

    public function testAlreadyRegisteredEnumMapping()
    {
        /** @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject $databasePlatformAbc */
        $databasePlatformAbc = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');

        $connectionAbc = $this->getMockBuilder('Doctrine\DBAL\Connection')
                              ->disableOriginalConstructor()
                              ->setMethods(['getDatabasePlatform'])
                              ->getMock();

        $connectionAbc->expects($this->once())
                      ->method('getDatabasePlatform')
                      ->willReturn($databasePlatformAbc);

        $this->doctrine->method('getConnections')
                       ->willReturn([$connectionAbc]);

        $databasePlatformAbc->registerDoctrineTypeMapping('enum', 'string');

        $bundle = new FreshDoctrineEnumBundle();
        $bundle->setContainer($this->container);
        $bundle->boot();

        $this->assertTrue($databasePlatformAbc->hasDoctrineTypeMappingFor('enum'));
        $this->assertEquals('string', $databasePlatformAbc->getDoctrineTypeMapping('enum'));
    }

    public function testEnumMappingReregistrationToString()
    {
        /** @var AbstractPlatform|\PHPUnit_Framework_MockObject_MockObject $databasePlatformAbc */
        $databasePlatformAbc = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');

        $connectionAbc = $this->getMockBuilder('Doctrine\DBAL\Connection')
                              ->disableOriginalConstructor()
                              ->setMethods(['getDatabasePlatform'])
                              ->getMock();

        $connectionAbc->expects($this->once())
                      ->method('getDatabasePlatform')
                      ->willReturn($databasePlatformAbc);

        $this->doctrine->method('getConnections')
                       ->willReturn([$connectionAbc]);

        $databasePlatformAbc->registerDoctrineTypeMapping('enum', 'boolean');

        $bundle = new FreshDoctrineEnumBundle();
        $bundle->setContainer($this->container);
        $bundle->boot();

        $this->assertTrue($databasePlatformAbc->hasDoctrineTypeMappingFor('enum'));
        $this->assertEquals('string', $databasePlatformAbc->getDoctrineTypeMapping('enum'));
    }
}
