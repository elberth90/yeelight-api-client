<?php

namespace Tests\Bulb;

use Socket\Raw\Socket;
use Yeelight\Bulb\Bulb;
use Yeelight\Bulb\BulbProperties;
use Yeelight\Bulb\Response;

/**
 * @property Bulb                                     bulb
 * @property \Prophecy\Prophecy\ObjectProphecy|Socket socket
 */
class BulbTest extends \PHPUnit_Framework_TestCase
{
    const SUCCESS_RESPONSE = '{"id": 0, "result":{}}';
    const ERROR_RESPONSE = '{"id": 0, "error":{"code":-5000,"message":"general error"}}';

    public function setUp()
    {
        $this->socket = $this->prophesize(Socket::class);
        $this->bulb = new Bulb($this->socket->reveal(), '192.168.1.2', 55443, '0x0');
    }

    public function test_getProp()
    {
        $properties = [BulbProperties::BRIGHT, BulbProperties::SATURATION, 'foo'];
        $response = ['id' => 1, 'result' => [100, 100, '']];
        $expected = new Response($response);

        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'get_prop', 'params' => $properties
                ]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(json_encode($response))->shouldBeCalled();

        $result = $this->bulb->getProp($properties);
        $this->assertEquals($expected, $result);
    }

    public function test_setCtAbx()
    {
        $ctValue = 1700;
        $effect = Bulb::EFFECT_SMOOTH;
        $duration = 30;
        $buffer = json_encode(
            ['id' => hexdec($this->bulb->getId()), 'method' => 'set_ct_abx', 'params' => [
                $ctValue, $effect, $duration
            ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setCtAbx($ctValue, $effect, $duration);
    }

    public function test_setCtAbx_can_handle_error_from_server()
    {
        $ctValue = -100;
        $effect = 'foo';
        $duration = 10;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_ct_abx', 'params' => [
                    $ctValue, $effect, $duration
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::ERROR_RESPONSE)->shouldBeCalled();

        $response = $this->bulb->setCtAbx($ctValue, $effect, $duration);
        $this->assertFalse($response->isSuccess());
    }

    public function test_setRgb()
    {
        $rgbValue =  0;
        $effect = Bulb::EFFECT_SUDDEN;
        $duration = 1000;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_rgb', 'params' => [
                    $rgbValue, $effect, $duration
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setRgb($rgbValue, $effect, $duration);
    }

    public function test_setHsv()
    {
        $hue = 0;
        $sat = 0;
        $effect = Bulb::EFFECT_SMOOTH;
        $duration = 1000;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_hsv', 'params' => [
                    $hue, $sat, $effect, $duration
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setHsv($hue, $sat, $effect, $duration);
    }

    public function test_setBright()
    {
        $brightness = 50;
        $effect = Bulb::EFFECT_SMOOTH;
        $duration = 1000;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_bright', 'params' => [
                    $brightness, $effect, $duration
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setBright($brightness, $effect, $duration);
    }

    public function test_setPower()
    {
        $power = Bulb::ON;
        $effect = Bulb::EFFECT_SMOOTH;
        $duration = 1000;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_power', 'params' => [
                    $power, $effect, $duration
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setPower($power, $effect, $duration);
    }

    public function test_toggle()
    {
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'toggle', 'params' => []])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->toggle();
    }

    public function test_setDefault()
    {
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'set_default', 'params' => []])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setDefault();
    }

    public function test_startCf()
    {
        $count = 2;
        $action = Bulb::ACTION_BEFORE;
        $flowExpression = [
            [1000, 2, 2700, 100],
            [500, 1, 255, 10],
        ];
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'start_cf', 'params' => [
                    $count, $action, '1000,2,2700,100,500,1,255,10'
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->startCf($count, $action, $flowExpression);
    }

    public function test_stopCf()
    {
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'stop_cf', 'params' => []])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->stopCf();
    }

    public function test_setScene()
    {
        $params = ['color', 65280, 70];
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_scene', 'params' => $params]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setScene($params);
    }

    public function test_cronAdd()
    {
        $type = 0;
        $value = 15;
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'cron_add', 'params' => [
            $type, $value
            ]])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->cronAdd($type, $value);
    }

    public function test_cronGet()
    {
        $type = 0;
        $response = ['id' => 1, 'result' => ['type' => 0, 'delay' => 15, 'mix' => 0]];
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'cron_get', 'params' => [
                $type
            ]])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(json_encode($response))->shouldBeCalled();

        $result = $this->bulb->cronGet($type);
        $this->assertEquals(new Response($response), $result);
    }

    public function test_cronDel()
    {
        $type = 0;
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'cron_del', 'params' => [
                $type
            ]])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->cronDel($type);
    }

    public function test_setAdjust()
    {
        $action = Bulb::ADJUST_ACTION_INCREASE;
        $prop = Bulb::ADJUST_ACTION_CIRCLE;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_adjust', 'params' => [
                    $action, $prop
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setAdjust($action, $prop);
    }

    public function test_setMusic()
    {
        $action = 0;
        $host = '192.168.0.2';
        $port = 54321;
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_music', 'params' => [
                    $action, $host, $port
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setMusic($action, $host, $port);
    }

    public function test_setName()
    {
        $name = 'foo';
        $buffer = json_encode(
                ['id' => hexdec($this->bulb->getId()), 'method' => 'set_name', 'params' => [
                    $name
                ]]
            )."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::SUCCESS_RESPONSE)->shouldBeCalled();

        $this->bulb->setName($name);
    }
}
