<?php

namespace Yeelight\Bulb;

use React\Promise\Promise;
use Socket\Raw\Socket;

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
     * This method is used to retrieve current property of smart LED
     *
     * @param array $properties The parameter is a list of property (consts from BulbProperties) names and the response
     *                          contains a list of corresponding property values. If the requested property name is not
     *                          recognized by smart LED, then a empty string value ("") will be returned
     *
     * @return Promise
     */
    public function getProp(array $properties)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'get_prop',
            'params' => $properties,
        ];
        $this->send($data);

        return $this->read();
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
     * @return Promise
     */
    private function read(): Promise
    {
        return new Promise(function (callable $resolve, callable $reject) {
            $response = new Response(
                json_decode($this->socket->read(self::PACKET_LENGTH), true)
            );

            if ($response->isSuccess()) {
                $resolve($response);

                return;
            }
            $reject($response->getException());
        });
    }

    /**
     * This method is used to change the color temperature of a smart LED
     *
     * @param int    $ctValue  is the target color temperature
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to change the color of a smart LED
     *
     * @param int    $rgbValue is the target color, whose type is integer. It should be expressed in decimal integer
     *                         ranges from 0 to 16777215 (hex: 0xFFFFFF).
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to change the color of a smart LED
     *
     * @param int    $hue      is the target hue value, whose type is integer
     * @param int    $sat      is the target saturation value whose type is integer
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to change the brightness of a smart LED
     *
     * @param int    $brightness is the target brightness. The type is integer and ranges from 1 to 100. The brightness
     *                           is a percentage instead of a absolute value.
     * @param string $effect     support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration   specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to switch on or off the smart LED (software managed on/off)
     *
     * @param string $power    can only be "on" or "off". "on" means turn on the smart LED, "off" means turn off the
     *                         smart LED
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to toggle the smart LED
     *
     * @return Promise
     */
    public function toggle()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'toggle',
            'params' => [],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to save current state of smart LED in persistent memory. So if user powers off and then
     * powers on the smart LED again (hard power reset), the smart LED will show last saved state
     *
     * @return Promise
     */
    public function setDefault()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_default',
            'params' => [],
        ];
        $this->send($data);

        return $this->read();
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
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to stop a running color flow
     *
     * @return Promise
     */
    public function stopCf()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'stop_cf',
            'params' => [],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to set the smart LED directly to specified state. If the smart LED is off, then it will turn
     * on the smart LED firstly and then apply the specified command
     *
     * @param array $params array that firs element is a class (color, hsv, ct, cf, auto_dealy_off) and next 3 are
     *                      class specific eg.
     *                      ['color', 65280, 70]
     *                      ['hsv', 300, 70, 100]
     *                      ['ct', 5400, 100]
     *                      ['cf',0,0,"500,1,255,100,1000,1,16776960,70"]
     *                      ['auto_delay_off', 50, 5]
     *
     * @return Promise
     */
    public function setScene(array $params)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_scene',
            'params' => $params,
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to start a timer job on the smart LED
     *
     * @param int $type  type of the cron job
     * @param int $value length of the timer (in minutes)
     *
     * @return Promise
     */
    public function cronAdd(int $type, int $value)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'cron_add',
            'params' => [
                $type,
                $value,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to retrieve the setting of the current cron job of the specified type
     *
     * @param int $type type of the cron job
     *
     * @return Promise
     */
    public function cronGet(int $type)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'cron_get',
            'params' => [
                $type,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to stop the specified cron job
     *
     * @param int $type type of the cron job
     *
     * @return Promise
     */
    public function cronDel(int $type)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'cron_del',
            'params' => [
                $type,
            ],
        ];
        $this->send($data);

        return $this->read();
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
     * @return Promise
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

        return $this->read();
    }

    /**
     * This method is used to start or stop music mode on a device
     *
     * @param int         $action the action of set_music command
     * @param string|null $host   the IP address of the music server
     * @param int|null    $port   the TCP port music application is listening on
     *
     * @return Promise
     */
    public function setMusic(int $action, string $host = null, int $port = null)
    {
        $params = [
            $action,
        ];

        if (!is_null($host)) {
            $params[] = $host;
        }

        if (!is_null($port)) {
            $params[] = $port;
        }

        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_music',
            'params' => $params,
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to name the device
     *
     * @param string $name name of the device
     *
     * @return Promise
     */
    public function setName(string $name)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_name',
            'params' => [
                $name,
            ],
        ];
        $this->send($data);

        return $this->read();
    }
}
