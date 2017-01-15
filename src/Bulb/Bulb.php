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
        $data = json_encode($data)."\r\n";
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
     * @param int    $ctValue
     * @param string $effect
     * @param int    $duration
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
     * @param int    $rgbValue
     * @param string $effect
     * @param int    $duration
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
     * @param int    $hue
     * @param int    $sat
     * @param string $effect
     * @param int    $duration
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
     * @param int    $brightness
     * @param string $effect
     * @param int    $duration
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
     * @param string $power
     * @param string $effect
     * @param int    $duration
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
     * @param int   $count
     * @param int   $action
     * @param array $flowExpression
     *
     * @throws BulbCommandException
     */
    public function startCf(int $count, int $action, array $flowExpression)
    {
        $state = implode(",", array_map(function($item) {return implode(",", $item);}, $flowExpression));
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'start_cf',
            'params' => [
                $count,
                $action,
                $state
            ],
        ];
        $this->send($data);
        $this->read();
    }

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
     * @param string $action
     * @param string $prop
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
                $prop
            ],
        ];
        $this->send($data);
        $this->read();
    }
}
