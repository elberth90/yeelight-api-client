<?php

namespace Yeelight\Bulb;

use Socket\Raw\Socket;
use Yeelight\Bulb\Exceptions\BulbCommandException;

class Bulb
{
    const PACKET_LENGTH = 4096;
    const NO_FLAG = 0;

    const EFFECT_SUDDEN = 'sudden';
    const EFFECT_SMOOTH = 'smooth';
    const ON = 'on';
    const OFF = 'off';
    const ACTION_BEFORE = 0;
    const ACTION_AFTER = 1;
    const ACTION_TURN_OFF = 2;
    const ADJUST_ACTION_INCREASE = 'increase';
    const ADJUST_ACTION_DECREASE = 'decrease';
    const ADJUST_ACTION_CIRCLE = 'circle';
    const ADJUST_PROP_BRIGHTNESS = 'bright';
    const ADJUST_PROP_COLOR_TEMP = 'ct';
    const ADJUST_PROP_COLOR = 'color';

    /**
     * @var Socket
     */
    private $socket;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $id;

    /**
     * Bulb constructor.
     *
     * @param Socket $socket
     * @param string $ip
     * @param int    $port
     * @param string $id
     */
    public function __construct(Socket $socket, string $ip, int $port, string $id)
    {
        $this->socket = $socket;
        $this->ip = $ip;
        $this->port = $port;
        $this->id = $id;

        $this->socket->connect($this->getAddress());
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return sprintf('%s:%d', $this->getIp(), $this->getPort());
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * This method is used to change the color temperature of a smart LED
     *
     * @param int    $ctValue  is the target color temperature
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @throws BulbCommandException
     */
    public function setCtAbx(int $ctValue, string $effect, int $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_ct_abx',
            'params' => [
                $ctValue,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param array $data
     */
    private function send(array $data)
    {
        $data = json_encode($data) . "\r\n";
        $this->socket->send($data, self::NO_FLAG);
    }

    /**
     * @return array
     * @throws BulbCommandException
     */
    private function read(): array
    {
        $response = json_decode($this->socket->read(self::PACKET_LENGTH), true);
        if (isset($response['error'])) {
            throw new BulbCommandException(
                $response['error']['message'],
                $response['error']['code'],
                $response['id']
            );
        }

        return $response;
    }

    /**
     * This method is used to change the color of a smart LED
     *
     * @param int    $rgbValue is the target color, whose type is integer. It should be expressed in decimal integer
     *                         ranges from 0 to 16777215 (hex: 0xFFFFFF).
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @throws BulbCommandException
     */
    public function setRgb(int $rgbValue, string $effect, int $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_rgb',
            'params' => [
                $rgbValue,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to change the color of a smart LED
     *
     * @param int    $hue      is the target hue value, whose type is integer
     * @param int    $sat      is the target saturation value whose type is integer
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @throws BulbCommandException
     */
    public function setHsv(int $hue, int $sat, string $effect, int $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_hsv',
            'params' => [
                $hue,
                $sat,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to change the brightness of a smart LED
     *
     * @param int    $brightness is the target brightness. The type is integer and ranges from 1 to 100. The brightness
     *                           is a percentage instead of a absolute value.
     * @param string $effect     support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration   specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @throws BulbCommandException
     */
    public function setBright(int $brightness, string $effect, int $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_bright',
            'params' => [
                $brightness,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to switch on or off the smart LED (software managed on/off)
     *
     * @param string $power    can only be "on" or "off". "on" means turn on the smart LED, "off" means turn off the
     *                         smart LED
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @throws BulbCommandException
     */
    public function setPower(string $power, string $effect, int $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_power',
            'params' => [
                $power,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to toggle the smart LED
     */
    public function toggle()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'toggle',
            'params' => [],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to save current state of smart LED in persistent memory. So if user powers off and then
     * powers on the smart LED again (hard power reset), the smart LED will show last saved state
     */
    public function setDefault()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_default',
            'params' => [],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to start a color flow. Color flow is a series of smart LED visible state changing. It can be
     * brightness changing, color changing or color temperature changing
     *
     * @param int   $count              is the total number of visible state changing before color flow stopped. 0
     *                                  means infinite loop on the state changing
     * @param int   $action             is the action taken after the flow is stopped
     *                                  0 means smart LED recover to the state before the color flow started
     *                                  1 means smart LED stay at the state when the flow is stopped
     *                                  2 means turn off the smart LED after the flow is stopped
     * @param array $flowExpression     is the expression of the state changing series in format
     *                                  [
     *                                  [duration, mode, value, brightness],
     *                                  [duration, mode, value, brightness]
     *                                  ]
     *
     * @throws BulbCommandException
     */
    public function startCf(int $count, int $action, array $flowExpression)
    {
        $state = implode(",", array_map(function ($item) {
            return implode(",", $item);
        }, $flowExpression));
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'start_cf',
            'params' => [
                $count,
                $action,
                $state,
            ],
        ];
        $this->send($data);
        $this->read();
    }

    /**
     * This method is used to stop a running color flow
     */
    public function stopCf()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'stop_cf',
            'params' => [],
        ];
        $this->send($data);
        $this->read();
    }


    /**
     * This method is used to change brightness, CT or color of a smart LED without knowing the current value, it's
     * main used by controllers.
     *
     * @param string $action the direction of the adjustment The valid value can be:
     *                       “increase": increase the specified property (Bulb::ADJUST_ACTION_INCREASE)
     *                       “decrease": decrease the specified property (Bulb::ADJUST_ACTION_DECREASE)
     *                       “circle": increase the specified property, after it reaches the max
     *                       (Bulb::ADJUST_ACTION_CIRCLE)
     * @param string $prop   the property to adjust. The valid value can be:
     *                       “bright": adjust brightness (Bulb::ADJUST_PROP_BRIGHTNESS)
     *                       “ct": adjust color temperature (Bulb::ADJUST_PROP_COLOR_TEMP)
     *                       “color": adjust color. (Bulb::ADJUST_PROP_COLOR) (When “prop" is “color", the “action" can
     *                       only be “circle", otherwise, it will be deemed as invalid request.)
     *
     * @throws BulbCommandException
     */
    public function setAdjust(string $action, string $prop)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_adjust',
            'params' => [
                $action,
                $prop,
            ],
        ];
        $this->send($data);
        $this->read();
    }
}
