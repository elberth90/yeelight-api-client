<?php

namespace tests;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Socket\Raw\Socket;
use Yeelight\Bulb\Bulb;
use Yeelight\Bulb\BulbFactory;
use Yeelight\YeelightRawClient;

/**
 * @property YeelightRawClient                             client
 * @property \Prophecy\Prophecy\ObjectProphecy|Socket      socket
 * @property int                                           readTimeout
 * @property \Prophecy\Prophecy\ObjectProphecy|BulbFactory bulbFactory
 */
class YeelightRawClientTest extends \PHPUnit_Framework_TestCase
{
    const RESPONSE = "HTTP/1.1 200 OK
        Cache-Control: max-age=3600
        Date:
        Ext:
        Location: yeelight://192.168.1.102:55443
        Server: POSIX UPnP/1.0 YGLC/1
        id: 0x0000000000000000
        model: color
        fw_ver: 45
        support: get_prop set_default set_power toggle set_bright start_cf stop_cf set_scene cron_add cron_get cron_del set_ct_abx set_rgb set_hsv set_adjust set_music set_name
        power: on
        bright: 100
        color_mode: 2
        ct: 2926
        rgb: 5728000
        hue: 359
        sat: 100
        name:";

    public function setUp()
    {
        $this->readTimeout = 2;
        $this->socket = $this->prophesize(Socket::class);
        $this->bulbFactory = $this->prophesize(BulbFactory::class);
        $this->client = new YeelightRawClient(
            $this->readTimeout,
            $this->socket->reveal(),
            $this->bulbFactory->reveal()
        );
    }

    public function test_searchForBulb_will_return_list_of_bulbs()
    {
        $this->socket
            ->sendTo(
                YeelightRawClient::DISCOVERY_RESPONSE,
                YeelightRawClient::NO_FLAG,
                YeelightRawClient::MULTICAST_ADDRESS
            )->shouldBeCalled();

        $this->socket->setBlocking(false)->shouldBeCalled();
        $this->socket->selectRead($this->readTimeout)->willReturn(true, false)->shouldBeCalled();
        $this->socket->read(YeelightRawClient::PACKET_LENGTH)->willReturn(self::RESPONSE)->shouldBeCalled();
        $this->bulbFactory
            ->create(Argument::type('array'))
            ->will([$this, 'getBulb'])
            ->shouldBeCalled();

        $bulbList = $this->client->search();

        $this->assertCount(1, $bulbList);
        $bulb = reset($bulbList);
        $this->assertInstanceOf(Bulb::class, $bulb);
    }

    /**
     * @return ObjectProphecy
     */
    public function getBulb(): ObjectProphecy
    {
        /** @var ObjectProphecy|Bulb $bulb */
        $bulb = $this->prophesize(Bulb::class);
        $bulb->getIp()->willReturn('192.168.1.102');

        return $bulb;
    }
}
