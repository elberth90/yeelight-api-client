<?php

namespace Tests\Bulb;

use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Yeelight\Bulb\BulbFactory;

/**
 * @property \Prophecy\Prophecy\ObjectProphecy|Factory socketFactory
 * @property \Prophecy\Prophecy\ObjectProphecy|Socket  socket
 * @property BulbFactory                               factory
 */
class BulbFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->socketFactory = $this->prophesize(Factory::class);
        $this->socket = $this->prophesize(Socket::class);
        $this->factory = new BulbFactory($this->socketFactory->reveal());
    }

    public function test_that_factory_can_create_Bulb()
    {
        $data = [
            'Location' => 'yeelight://192.168.1.239:55443',
            'id' => '0x0000000000000000',
        ];
        $this->socketFactory->createTcp4()->willReturn($this->socket->reveal())->shouldBeCalled();

        $bulb = $this->factory->create($data);
        $this->assertEquals('192.168.1.239', $bulb->getIp());
        $this->assertEquals(55443, $bulb->getPort());
        $this->assertEquals('0x0000000000000000', $bulb->getId());
    }
}
