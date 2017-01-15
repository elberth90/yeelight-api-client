<?php

namespace Tests\Bulb;

use Socket\Raw\Socket;
use Yeelight\Bulb\Bulb;

/**
 * @property Bulb                                     bulb
 * @property \Prophecy\Prophecy\ObjectProphecy|Socket socket
 */
class BulbTest extends \PHPUnit_Framework_TestCase
{
    const EMPTY_RESPONSE = '{}';
    const ERROR_RESPONSE = '{"id": 0, "error":{"code":-5000,"message":"general error"}}';

    public function setUp()
    {
        $this->socket = $this->prophesize(Socket::class);
        $this->bulb = new Bulb($this->socket->reveal(), '192.168.1.2', 55443, '0x0');
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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

        $this->bulb->setCtAbx($ctValue, $effect, $duration);
    }

    /**
     * @expectedException \Yeelight\Bulb\Exceptions\BulbCommandException
     */
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

        $this->bulb->setCtAbx($ctValue, $effect, $duration);
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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

        $this->bulb->setPower($power, $effect, $duration);
    }

    public function test_toggle()
    {
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'toggle', 'params' => []])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

        $this->bulb->toggle();
    }

    public function test_setDefault()
    {
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'set_default', 'params' => []])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

        $this->bulb->startCf($count, $action, $flowExpression);
    }

    public function test_stopCf()
    {
        $buffer = json_encode(['id' => hexdec($this->bulb->getId()), 'method' => 'stop_cf', 'params' => []])."\r\n";
        $this->socket->send($buffer, Bulb::NO_FLAG)->shouldBeCalled();
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

        $this->bulb->stopCf();
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
        $this->socket->read(Bulb::PACKET_LENGTH)->willReturn(self::EMPTY_RESPONSE)->shouldBeCalled();

        $this->bulb->setAdjust($action, $prop);
    }
}
