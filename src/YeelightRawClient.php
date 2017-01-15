<?php

namespace Yeelight;

use Socket\Raw\Socket;
use Yeelight\Bulb\Bulb;
use Yeelight\Bulb\BulbFactory;
use Yeelight\Exceptions\SocketException;

class YeelightRawClient
{
    const DISCOVERY_RESPONSE = "M-SEARCH * HTTP/1.1\r\n
        HOST: 239.255.255.250:1982\r\n
        MAN: \"ssdp:discover\"\r\n
        ST: wifi_bulb\r\n";
    const MULTICAST_ADDRESS = '239.255.255.250:1982';
    const NO_FLAG = 0;
    const PACKET_LENGTH = 4096;

    /**
     * @var Bulb[]
     */
    private $bulbList = [];

    /**
     * @var int
     */
    private $readTimeout;

    /**
     * @var Socket
     */
    private $socket;

    /**
     * @var BulbFactory
     */
    private $bulbFactory;

    /**
     * YeelightClient constructor.
     *
     * @param int         $readTimeout
     * @param Socket      $socket
     * @param BulbFactory $bulbFactory
     */
    public function __construct(int $readTimeout, Socket $socket, BulbFactory $bulbFactory)
    {
        $this->readTimeout = $readTimeout;
        $this->socket = $socket;
        $this->bulbFactory = $bulbFactory;
    }

    /**
     * @return Bulb[]
     * @throws SocketException
     */
    public function search(): array
    {
        $this->socket->sendTo(self::DISCOVERY_RESPONSE, self::NO_FLAG, self::MULTICAST_ADDRESS);
        $this->socket->setBlocking(false);
        while ($this->socket->selectRead($this->readTimeout)) {
            $data = $this->formatResponse($this->socket->read(self::PACKET_LENGTH));
            $bulb = $this->bulbFactory->create($data);
            $this->bulbList[$bulb->getIp()] = $bulb;
        }

        return $this->bulbList;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    private function formatResponse(string $data): array
    {
        return array_reduce(explode("\n", trim($data)), function ($carry, $item) {
            $res = explode(':', $item, 2);
            $carry[trim(reset($res))] = end($res);

            return $carry;
        }, []);
    }
}
